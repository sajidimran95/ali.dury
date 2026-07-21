<div class="pos-tab-content">
    <div class="row">
        <div class="col-sm-12">
            <h4>Ecommerce Storefront</h4>
            <p class="help-block">@lang('ecommerce::lang.enable_ecommerce_help')</p>
            <div class="callout callout-info">
                <p><strong>Setup checklist:</strong></p>
                <ol>
                    <li>Open Ecommerce Settings and turn <strong>Enable Ecommerce</strong> ON</li>
                    <li>Select a <strong>fulfillment location</strong> (uses the same POS stock)</li>
                    <li>On each product, set <strong>Sales Channel</strong> to POS + Ecommerce or Ecommerce Only</li>
                    <li>Public homepage becomes the store at <code>/</code> — staff login stays at <code>/login</code></li>
                </ol>
            </div>
            <p>
                <a href="{{ url('/ecommerce/settings') }}" class="tw-dw-btn tw-dw-btn-primary tw-text-white">
                    Open Ecommerce Settings
                </a>
                <a href="{{ url('/') }}" target="_blank" class="tw-dw-btn tw-dw-btn-outline">
                    Preview Storefront
                </a>
            </p>
        </div>
    </div>
</div>
