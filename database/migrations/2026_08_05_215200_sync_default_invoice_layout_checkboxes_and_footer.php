<?php

use App\BusinessLocation;
use App\InvoiceLayout;
use App\Utils\InvoiceLayoutDefaults;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Apply canonical default invoice checkbox + footer settings to all businesses,
     * and point every location at that business's default layout.
     */
    public function up(): void
    {
        if (! Schema::hasTable('invoice_layouts')) {
            return;
        }

        $attrs = InvoiceLayoutDefaults::syncAttributes();

        // Update every layout flagged as default (preserves logo path / ids)
        InvoiceLayout::where('is_default', 1)->update($attrs);

        // Also update classic "Default" named layouts that may not be is_default yet
        InvoiceLayout::where('name', 'Default')->update($attrs);

        // Ensure one default per business and apply to all locations
        $businessIds = InvoiceLayout::query()
            ->select('business_id')
            ->distinct()
            ->pluck('business_id');

        foreach ($businessIds as $businessId) {
            $layout = InvoiceLayout::where('business_id', $businessId)
                ->where('is_default', 1)
                ->orderBy('id')
                ->first();

            if (empty($layout)) {
                $layout = InvoiceLayout::where('business_id', $businessId)
                    ->where('name', 'Default')
                    ->orderBy('id')
                    ->first();
            }

            if (empty($layout)) {
                $layout = InvoiceLayout::create(InvoiceLayoutDefaults::attributes((int) $businessId, true));
            } else {
                InvoiceLayout::where('business_id', $businessId)
                    ->where('id', '!=', $layout->id)
                    ->update(['is_default' => 0]);
                $layout->is_default = 1;
                $layout->save();
            }

            $locationUpdate = [
                'invoice_layout_id' => $layout->id,
            ];
            if (Schema::hasColumn('business_locations', 'sale_invoice_layout_id')) {
                $locationUpdate['sale_invoice_layout_id'] = $layout->id;
            }

            BusinessLocation::where('business_id', $businessId)->update($locationUpdate);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive: settings stay as-is after deploy sync.
    }
};
