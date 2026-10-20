<?php

namespace App\Utils;

/**
 * Canonical default invoice layout for new businesses, fresh-POS seed, and deploy sync.
 * Matches the configured Default layout (checkboxes + footer).
 */
class InvoiceLayoutDefaults
{
    /**
     * Footer printed on receipts / invoices.
     */
    public const FOOTER_TEXT = 'WISCONSIN TOBACCO/VAPOR PRODUCT EXCISE TAX INCLUDED SALE*';

    /**
     * Attributes for creating or updating a default InvoiceLayout row.
     * Does not include business-specific logo paths.
     *
     * @return array<string, mixed>
     */
    public static function attributes(int $businessId, bool $isDefault = true): array
    {
        return [
            'name' => 'Default',
            'header_text' => null,
            'invoice_no_prefix' => 'Invoice No.',
            'quotation_no_prefix' => null,
            'invoice_heading' => 'Invoice',
            'sub_heading_line1' => null,
            'sub_heading_line2' => null,
            'sub_heading_line3' => null,
            'sub_heading_line4' => null,
            'sub_heading_line5' => null,
            'invoice_heading_not_paid' => '',
            'invoice_heading_paid' => '',
            'quotation_heading' => null,
            'sub_total_label' => 'Subtotal',
            'discount_label' => 'Discount',
            'tax_label' => 'Tax',
            'total_label' => 'Total',
            'round_off_label' => null,
            'total_due_label' => 'Due Amount',
            'paid_label' => 'Total Paid',
            'show_client_id' => 0,
            'client_id_label' => null,
            'client_tax_label' => null,
            'date_label' => 'Date',
            'date_time_format' => null,
            'show_time' => 1,
            // Product / line fields
            'show_brand' => 1,
            'show_sku' => 1,
            'show_cat_code' => 1,
            'show_expiry' => 0,
            'show_lot' => 0,
            'show_image' => 0,
            'show_sale_description' => 0,
            'sales_person_label' => null,
            'show_sales_person' => 1,
            'table_product_label' => 'Product',
            'table_qty_label' => 'Quantity',
            'table_unit_price_label' => 'Unit Price',
            'table_subtotal_label' => 'Subtotal',
            'cat_code_label' => null,
            // Header / location / tax
            'show_logo' => 1,
            'show_business_name' => 1,
            'show_location_name' => 1,
            'show_landmark' => 1,
            'show_city' => 1,
            'show_state' => 1,
            'show_zip_code' => 1,
            'show_country' => 1,
            'show_mobile_number' => 1,
            'show_alternate_number' => 0,
            'show_email' => 0,
            'show_tax_1' => 1,
            'show_tax_2' => 0,
            'show_barcode' => 1,
            'show_payments' => 1,
            'show_customer' => 1,
            'customer_label' => 'Customer',
            'commission_agent_label' => null,
            'show_commission_agent' => 0,
            'show_reward_point' => 1,
            'highlight_color' => '#000000',
            'footer_text' => self::FOOTER_TEXT,
            'module_info' => null,
            'common_settings' => [
                'show_due_date' => '1',
                'show_total_in_words' => '1',
                'num_to_word_format' => 'international',
            ],
            'is_default' => $isDefault ? 1 : 0,
            'business_id' => $businessId,
            'show_letter_head' => 0,
            'show_qr_code' => 0,
            'qr_code_fields' => null,
            'design' => 'us_letter',
            'cn_heading' => null,
            'cn_no_label' => null,
            'cn_amount_label' => null,
            'table_tax_headings' => null,
            'show_previous_bal' => 1,
            'prev_bal_label' => null,
            'show_previous_balance_due' => 1,
            'previous_balance_due_label' => null,
            'change_return_label' => null,
            'product_custom_fields' => null,
            'contact_custom_fields' => null,
            'location_custom_fields' => null,
        ];
    }

    /**
     * Fields safe to mass-update on existing rows (preserve id, logo, business_id, times).
     *
     * @return array<string, mixed>
     */
    public static function syncAttributes(): array
    {
        $attrs = self::attributes(0, true);
        unset($attrs['business_id'], $attrs['is_default'], $attrs['name']);

        // Eloquent cast on InvoiceLayout handles array → JSON for common_settings on model save;
        // for query builder update, encode JSON.
        if (isset($attrs['common_settings']) && is_array($attrs['common_settings'])) {
            $attrs['common_settings'] = json_encode($attrs['common_settings']);
        }

        return $attrs;
    }
}
