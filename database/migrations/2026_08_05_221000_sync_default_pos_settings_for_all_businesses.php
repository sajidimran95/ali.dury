<?php

use App\Business;
use App\Utils\PosSettingsDefaults;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Apply canonical POS settings (checkboxes, shortcuts, PWA, scale)
     * to every existing business so old installs match after git pull + migrate.
     */
    public function up(): void
    {
        if (! Schema::hasTable('business')) {
            return;
        }

        $shortcuts = json_encode(PosSettingsDefaults::keyboardShortcuts());
        $weighing = PosSettingsDefaults::weighingScaleSetting();

        Business::query()->orderBy('id')->each(function (Business $business) use ($shortcuts, $weighing) {
            $existing = [];
            if (! empty($business->pos_settings)) {
                $decoded = is_array($business->pos_settings)
                    ? $business->pos_settings
                    : json_decode($business->pos_settings, true);
                if (is_array($decoded)) {
                    $existing = $decoded;
                }
            }

            $business->pos_settings = json_encode(PosSettingsDefaults::mergeOntoExisting($existing));
            $business->keyboard_shortcuts = $shortcuts;
            $business->weighing_scale_setting = $weighing;
            $business->save();
        });
    }

    public function down(): void
    {
        // Non-destructive: installed settings stay after sync.
    }
};
