<?php

namespace App\Services\Msa;

use App\Business;
use App\Category;
use App\User;

class MsaSetupService
{
    /**
     * Create/restore Tobacco MSA categories, custom field labels, and common settings.
     *
     * @return int Tobacco parent category id
     */
    public function setupForBusiness(Business $business): int
    {
        $parentId = $this->ensureCategories((int) $business->id);
        $this->ensureCustomLabels($business);
        $this->ensureCommonSettings($business, $parentId);

        return $parentId;
    }

    public function ensureCategories(int $businessId): int
    {
        $parent = Category::withTrashed()
            ->where('business_id', $businessId)
            ->where('category_type', 'product')
            ->where('name', 'Tobacco')
            ->where('parent_id', 0)
            ->first();

        if ($parent && $parent->trashed()) {
            $parent->restore();
        }

        if (! $parent) {
            $parent = Category::create([
                'name' => 'Tobacco',
                'business_id' => $businessId,
                'short_code' => 'TOB',
                'parent_id' => 0,
                'created_by' => User::where('business_id', $businessId)->value('id') ?: 1,
                'category_type' => 'product',
            ]);
        }

        $subs = [
            ['name' => 'Cigarettes', 'short_code' => 'CIG', 'msa' => '003231'],
            ['name' => 'Moist Spit Tobacco', 'short_code' => 'MST', 'msa' => '003211'],
            ['name' => 'Cigars', 'short_code' => 'CGR', 'msa' => '003251'],
            ['name' => 'Little Filtered Cigars', 'short_code' => 'LFC', 'msa' => '003252'],
            ['name' => 'E-Cigarettes', 'short_code' => 'ECG', 'msa' => '003292'],
            ['name' => 'RYO Tobacco', 'short_code' => 'RYO', 'msa' => '003221'],
            ['name' => 'Pipe Tobacco', 'short_code' => 'PIP', 'msa' => '003241'],
        ];

        foreach ($subs as $sub) {
            $existing = Category::withTrashed()
                ->where('business_id', $businessId)
                ->where('category_type', 'product')
                ->where('parent_id', $parent->id)
                ->where('name', $sub['name'])
                ->first();

            if ($existing && $existing->trashed()) {
                $existing->restore();
            }

            if (! $existing) {
                Category::create([
                    'name' => $sub['name'],
                    'business_id' => $businessId,
                    'short_code' => $sub['short_code'],
                    'parent_id' => $parent->id,
                    'created_by' => User::where('business_id', $businessId)->value('id') ?: 1,
                    'category_type' => 'product',
                    'description' => 'MSA category code '.$sub['msa'],
                ]);
            } elseif (empty($existing->description)) {
                $existing->description = 'MSA category code '.$sub['msa'];
                $existing->save();
            }
        }

        return (int) $parent->id;
    }

    public function ensureCustomLabels(Business $business): void
    {
        $labels = $business->custom_labels;
        if (is_string($labels)) {
            $labels = json_decode($labels, true) ?: [];
        }
        if (! is_array($labels)) {
            $labels = [];
        }

        $labels['product'] = $labels['product'] ?? [];
        $labels['contact'] = $labels['contact'] ?? [];
        $labels['product_cf_details'] = $labels['product_cf_details'] ?? [];

        $productLabels = [
            1 => 'MSA Items Per Selling Unit',
            2 => 'MSA Category Code',
            3 => 'MSA Promo Code',
            4 => 'MSA Promo Description',
        ];
        foreach ($productLabels as $i => $label) {
            if (empty($labels['product']['custom_field_'.$i])) {
                $labels['product']['custom_field_'.$i] = $label;
            }
        }

        $labels['product_cf_details'][1] = [
            'type' => 'dropdown',
            'dropdown_options' => "200\n5\n10\n20\n25\n40\n60\n100\n250",
        ];
        $labels['product_cf_details'][2] = [
            'type' => 'dropdown',
            'dropdown_options' => "003231\n003211\n003251\n003252\n003292\n003221\n003241\n003213\n003212\n003214\n003215\n003227",
        ];
        $labels['product_cf_details'][3] = [
            'type' => 'text',
            'dropdown_options' => null,
        ];
        $labels['product_cf_details'][4] = [
            'type' => 'text',
            'dropdown_options' => null,
        ];

        if (empty($labels['contact']['custom_field_1'])) {
            $labels['contact']['custom_field_1'] = 'MSA Class Of Trade';
        }
        if (empty($labels['contact']['custom_field_3'])) {
            $labels['contact']['custom_field_3'] = 'MSA Cash And Carry (Y/N)';
        }

        $business->custom_labels = $labels;
        $business->save();
    }

    public function ensureCommonSettings(Business $business, int $tobaccoCategoryId): void
    {
        $common = $business->common_settings ?? [];
        if (! is_array($common)) {
            $common = [];
        }

        $common['msa_tobacco_category_id'] = $tobaccoCategoryId;

        if (empty($common['msa_state_tax'])) {
            $common['msa_state_tax'] = 'TN';
        }

        $admin = User::where('business_id', $business->id)->orderBy('id')->first();
        if ($admin) {
            if (empty($common['msa_contact_first']) && ! empty($admin->first_name)) {
                $common['msa_contact_first'] = $admin->first_name;
            }
            if (empty($common['msa_contact_last']) && ! empty($admin->surname)) {
                $common['msa_contact_last'] = $admin->surname;
            }
            if (empty($common['msa_contact_email']) && ! empty($admin->email)) {
                $common['msa_contact_email'] = $admin->email;
            }
        }

        $business->common_settings = $common;
        $business->save();
    }
}
