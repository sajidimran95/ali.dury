<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Combines Category and sub-category
     *
     * @param  int  $business_id
     * @param  bool  $only_pos_visible  When true, hide categories with show_in_pos = 0 (POS screen)
     * @return array
     */
    public static function catAndSubCategories($business_id, $only_pos_visible = false)
    {
        $all_categories = Category::where('business_id', $business_id)
                                ->where('category_type', 'product')
                                ->orderBy('name', 'asc')
                                ->get()
                                ->toArray();

        if (empty($all_categories)) {
            return [];
        }
        $categories = [];
        $sub_categories = [];

        foreach ($all_categories as $category) {
            if ($only_pos_visible && array_key_exists('show_in_pos', $category) && (int) $category['show_in_pos'] === 0) {
                continue;
            }
            if ($category['parent_id'] == 0) {
                $categories[] = $category;
            } else {
                $sub_categories[] = $category;
            }
        }

        $sub_cat_by_parent = [];
        if (! empty($sub_categories)) {
            foreach ($sub_categories as $sub_category) {
                if (empty($sub_cat_by_parent[$sub_category['parent_id']])) {
                    $sub_cat_by_parent[$sub_category['parent_id']] = [];
                }

                $sub_cat_by_parent[$sub_category['parent_id']][] = $sub_category;
            }
        }

        foreach ($categories as $key => $value) {
            if (! empty($sub_cat_by_parent[$value['id']])) {
                $subs = $sub_cat_by_parent[$value['id']];
                foreach ($subs as $sk => $sub) {
                    if (! empty($sub_cat_by_parent[$sub['id']])) {
                        $subs[$sk]['child_categories'] = $sub_cat_by_parent[$sub['id']];
                    }
                }
                $categories[$key]['sub_categories'] = $subs;
            }
        }

        return $categories;
    }

    /**
     * Top-level categories and their sub categories, for choosing a parent
     * (a sub category can be the parent of a child category).
     *
     * @return array<int, string> id => label ("Parent" or "Parent › Sub")
     */
    public static function parentOptions($business_id, $type, $exclude_id = null)
    {
        $rows = Category::where('business_id', $business_id)
            ->where('category_type', $type)
            ->when($exclude_id, fn ($q) => $q->where('id', '!=', $exclude_id))
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        $top = $rows->where('parent_id', 0);
        $options = [];
        foreach ($top as $cat) {
            $options[$cat->id] = $cat->name;
            foreach ($rows->where('parent_id', $cat->id) as $sub) {
                $options[$sub->id] = $cat->name.' › '.$sub->name;
            }
        }

        return $options;
    }

    /**
     * Category Dropdown
     *
     * @param  int  $business_id
     * @param  string  $type category type
     * @return array
     */
    public static function forDropdown($business_id, $type)
    {
        $categories = Category::where('business_id', $business_id)
                            ->where('parent_id', 0)
                            ->where('category_type', $type)
                            ->select(DB::raw('IF(short_code IS NOT NULL, CONCAT(name, "-", short_code), name) as name'), 'id')
                            ->orderBy('name', 'asc')
                            ->get();

        $dropdown = $categories->pluck('name', 'id');

        return $dropdown;
    }

    /**
     * Product categories flagged "Needed for MSA report" (stored in business common_settings).
     *
     * @return list<int>
     */
    public static function msaCategoryIds(int $business_id): array
    {
        $settings = Business::where('id', $business_id)->value('common_settings');
        $settings = is_string($settings) ? (json_decode($settings, true) ?: []) : (array) $settings;

        return array_values(array_unique(array_filter(array_map('intval', (array) ($settings['msa_tobacco_category_ids'] ?? $settings['msa_tobacco_category_id'] ?? [])))));
    }

    /**
     * Save the MSA category list; the MSA report preselects these next time.
     */
    public static function setMsaCategoryIds(int $business_id, array $category_ids): void
    {
        $category_ids = array_values(array_unique(array_filter(array_map('intval', $category_ids))));

        $business = Business::findOrFail($business_id);
        $common = $business->common_settings ?? [];
        $common['msa_tobacco_category_ids'] = $category_ids;
        if (count($category_ids) === 1) {
            $common['msa_tobacco_category_id'] = $category_ids[0];
        } else {
            unset($common['msa_tobacco_category_id']);
        }
        $business->common_settings = $common;
        $business->save();
    }

    /**
     * Add or remove one category from the MSA report list.
     */
    public static function toggleMsaCategory(int $business_id, int $category_id, bool $needed): void
    {
        $ids = self::msaCategoryIds($business_id);
        $ids = $needed
            ? array_merge($ids, [$category_id])
            : array_diff($ids, [$category_id]);

        self::setMsaCategoryIds($business_id, $ids);
    }

    /**
     * Parent + sub categories for multi-select pickers ("Parent -> Child").
     */
    public static function forDropdownWithSub(int $business_id, string $type = 'product'): array
    {
        $all = Category::where('business_id', $business_id)
            ->where('category_type', $type)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'parent_id']);
        $by_parent = $all->groupBy(fn ($c) => (int) $c->parent_id);

        $dropdown = [];
        foreach ($by_parent[0] ?? [] as $parent) {
            $dropdown[$parent->id] = $parent->name;
            foreach ($by_parent[$parent->id] ?? [] as $child) {
                $dropdown[$child->id] = $parent->name.' -> '.$child->name;
            }
        }

        return $dropdown;
    }

    public function sub_categories()
    {
        return $this->hasMany(\App\Category::class, 'parent_id');
    }

    /**
     * Scope a query to only include main categories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyParent($query)
    {
        return $query->where('parent_id', 0);
    }

    public static function hasMeasureUnitColumns(): bool
    {
        static $has = null;
        if ($has === null) {
            $has = \Schema::hasColumn('categories', 'measure_unit_enabled');
        }

        return $has;
    }

    /**
     * Find (or create) the base unit used to count stock for a category, e.g. "oz", "gram".
     */
    public static function resolveMeasureUnit(int $business_id, string $name, ?int $user_id = null): ?Unit
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $unit = Unit::where('business_id', $business_id)
            ->whereNull('base_unit_id')
            ->where(function ($q) use ($name) {
                $q->where('short_name', $name)->orWhere('actual_name', $name);
            })
            ->first();

        if (! $unit) {
            $user_id = $user_id ?: auth()->id() ?: (int) Business::where('id', $business_id)->value('owner_id');
            $unit = Unit::create([
                'business_id' => $business_id,
                'actual_name' => $name,
                'short_name' => $name,
                'allow_decimal' => 1,
                'created_by' => $user_id,
            ]);
        }

        return $unit;
    }

    /**
     * Unit id from the most specific selected category level that has a measurement unit.
     */
    public static function measureUnitIdFor(int $business_id, array $category_ids): ?int
    {
        if (! self::hasMeasureUnitColumns()) {
            return null;
        }

        $ids = array_values(array_filter(array_map('intval', $category_ids)));
        if ($ids === []) {
            return null;
        }

        $rows = Category::where('business_id', $business_id)
            ->whereIn('id', $ids)
            ->where('measure_unit_enabled', 1)
            ->whereNotNull('measure_unit_id')
            ->pluck('measure_unit_id', 'id');

        foreach (array_reverse($ids) as $id) {
            if (! empty($rows[$id])) {
                return (int) $rows[$id];
            }
        }

        return null;
    }

    /**
     * [category_id => ['unit_id' => .., 'name' => ..]] for the product form.
     */
    public static function measureUnitMap(int $business_id): array
    {
        if (! self::hasMeasureUnitColumns()) {
            return [];
        }

        return Category::where('business_id', $business_id)
            ->where('category_type', 'product')
            ->where('measure_unit_enabled', 1)
            ->whereNotNull('measure_unit_id')
            ->get(['id', 'measure_unit_id', 'measure_unit_name'])
            ->mapWithKeys(fn ($c) => [$c->id => ['unit_id' => (int) $c->measure_unit_id, 'name' => (string) $c->measure_unit_name]])
            ->all();
    }
}
