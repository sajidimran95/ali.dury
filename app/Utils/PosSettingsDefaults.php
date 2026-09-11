<?php

namespace App\Utils;

/**
 * Canonical POS settings used for new businesses, empty fallbacks,
 * and sync to existing installs (git pull + migrate).
 *
 * Matches Business Settings → POS tab (checkboxes, shortcuts, PWA, scale).
 */
class PosSettingsDefaults
{
    /**
     * Keyboard shortcuts on POS tab.
     *
     * @return array{pos: array<string, string|null>}
     */
    public static function keyboardShortcuts(): array
    {
        return [
            'pos' => [
                'express_checkout' => 'shift+e',
                'pay_n_ckeckout' => 'shift+p',
                'draft' => 'shift+d',
                'cancel' => 'shift+c',
                'recent_product_quantity' => 'f2',
                'weighing_scale' => null,
                'edit_discount' => 'shift+i',
                'edit_order_tax' => 'shift+t',
                'add_payment_row' => 'shift+r',
                'finalize_payment' => 'shift+f',
                'add_new_product' => 'f4',
            ],
        ];
    }

    /**
     * Weighing scale barcode lengths (POS tab).
     *
     * @return array<string, string|null>
     */
    public static function weighingScaleSetting(): array
    {
        return [
            'label_prefix' => null,
            'product_sku_length' => '5',
            'qty_length' => '4',
            'qty_length_decimal' => '3',
        ];
    }

    /**
     * Default pos_settings JSON keys (checkboxes + PWA + common POS flags).
     *
     * Active flags (from production POS tab):
     * - Disable Multiple Pay
     * - Disable Express Checkout
     * - Enable transaction date on POS screen
     * - Enable POS PWA
     *
     * @return array<string, mixed>
     */
    public static function posSettings(): array
    {
        return [
            // Checkboxes — active
            'disable_pay_checkout' => 1,
            'disable_express_checkout' => 1,
            'enable_transaction_date' => 1,
            'enable_pwa' => 1,

            // Checkboxes — inactive
            'disable_draft' => 0,
            'hide_product_suggestion' => 0,
            'hide_recent_trans' => 0,
            'disable_discount' => 0,
            'disable_order_tax' => 0,
            'disable_line_discount' => 0,
            'is_pos_subtotal_editable' => 0,
            'disable_suspend' => 0,
            'inline_service_staff' => 0,
            'is_service_staff_required' => 0,
            'disable_credit_sale_button' => 0,
            'enable_weighing_scale' => 0,
            'show_invoice_scheme' => 0,
            'show_invoice_layout' => 0,
            'print_on_suspend' => 0,
            'show_pricing_on_product_sugesstion' => 0,

            // PWA
            'pwa_app_name' => 'Japs POS',
            'pwa_short_name' => 'POS',
            'pwa_description' => 'Point of Sale system',
            'pwa_theme_color' => '#0f0f0f',
            'pwa_background_color' => '#0f0f0f',
            'pwa_icon' => null,

            // POS-related extras used by this install
            'allow_overselling' => 1,
            'enable_sales_order' => 1,
            'cmmsn_calculation_type' => 'invoice_value',
            'enable_cash_denomination_on' => 'pos_screen',
            'amount_rounding_method' => null,
            'display_screen_heading' => null,
            'cash_denominations' => null,
            'razor_pay_key_id' => null,
            'razor_pay_key_secret' => null,
            'stripe_public_key' => null,
            'stripe_secret_key' => null,
        ];
    }

    /**
     * Keys that must be forced onto existing businesses on migrate
     * (do not leave older checkbox values).
     *
     * @return list<string>
     */
    public static function forceKeys(): array
    {
        return [
            'disable_pay_checkout',
            'disable_express_checkout',
            'enable_transaction_date',
            'enable_pwa',
            'disable_draft',
            'hide_product_suggestion',
            'hide_recent_trans',
            'disable_discount',
            'disable_order_tax',
            'disable_line_discount',
            'is_pos_subtotal_editable',
            'disable_suspend',
            'inline_service_staff',
            'is_service_staff_required',
            'disable_credit_sale_button',
            'enable_weighing_scale',
            'show_invoice_scheme',
            'show_invoice_layout',
            'print_on_suspend',
            'show_pricing_on_product_sugesstion',
            'pwa_app_name',
            'pwa_short_name',
            'pwa_description',
            'pwa_theme_color',
            'pwa_background_color',
            'allow_overselling',
            'enable_sales_order',
            'cmmsn_calculation_type',
            'enable_cash_denomination_on',
        ];
    }

    /**
     * Merge canonical defaults into an existing pos_settings array.
     * Forces POS tab keys; keeps custom secrets/icons already set when non-empty.
     *
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>
     */
    public static function mergeOntoExisting(array $existing): array
    {
        $defaults = self::posSettings();
        $merged = array_merge($defaults, $existing);

        foreach (self::forceKeys() as $key) {
            if (array_key_exists($key, $defaults)) {
                $merged[$key] = $defaults[$key];
            }
        }

        // Keep custom PWA icon if already uploaded
        if (! empty($existing['pwa_icon'])) {
            $merged['pwa_icon'] = $existing['pwa_icon'];
        }

        // Keep payment gateway secrets if present
        foreach (['razor_pay_key_id', 'razor_pay_key_secret', 'stripe_public_key', 'stripe_secret_key', 'cash_denominations', 'display_screen_heading'] as $keep) {
            if (array_key_exists($keep, $existing) && $existing[$keep] !== null && $existing[$keep] !== '') {
                $merged[$keep] = $existing[$keep];
            }
        }

        return $merged;
    }
}
