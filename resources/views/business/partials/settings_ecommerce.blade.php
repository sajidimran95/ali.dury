<div class="pos-tab-content">
    <div class="row">
        <div class="col-sm-12">
            <p class="help-block">@lang('ecommerce::lang.enable_ecommerce_help')</p>
            <p>
                <a href="{{ url('/ecommerce/settings') }}" class="tw-dw-btn tw-dw-btn-primary tw-text-white">
                    Open Ecommerce Settings
                </a>
                <a href="{{ url('/shop') }}" target="_blank" class="tw-dw-btn tw-dw-btn-outline">
                    Preview Storefront (/shop)
                </a>
            </p>
            <ul>
                <li>Enable/disable the online store</li>
                <li>Set fulfillment location for shared POS inventory</li>
                <li>Configure COD / Stripe / PayPal</li>
                <li>Manage shipping &amp; USA tax rules</li>
            </ul>
            <p class="text-muted">On each product, set <strong>Sales Channel</strong> to POS Only, POS + Ecommerce, or Ecommerce Only.</p>
        </div>
    </div>
</div>
