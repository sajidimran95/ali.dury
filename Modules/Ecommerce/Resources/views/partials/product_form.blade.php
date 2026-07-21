<div class="row" id="ecommerce_product_fields">
    <div class="col-md-12">
        <h4 class="tw-mt-2">@lang('ecommerce::lang.ecom_fields')</h4>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            {!! Form::label('sales_channel', __('ecommerce::lang.sales_channel') . ':') !!}
            {!! Form::select('sales_channel', [
                'pos_only' => __('ecommerce::lang.pos_only'),
                'pos_ecommerce' => __('ecommerce::lang.pos_ecommerce'),
                'ecommerce_only' => __('ecommerce::lang.ecommerce_only'),
            ], !empty($product) ? ($product->sales_channel ?? 'pos_only') : (!empty($duplicate_product) ? ($duplicate_product->sales_channel ?? 'pos_only') : 'pos_only'), ['class' => 'form-control select2', 'id' => 'sales_channel']); !!}
        </div>
    </div>
    <div class="col-sm-4 ecom-only-fields">
        <div class="form-group">
            {!! Form::label('ecom_slug', __('ecommerce::lang.url_slug') . ':') !!}
            {!! Form::text('ecom_slug', !empty($product) ? $product->ecom_slug : (!empty($duplicate_product) ? $duplicate_product->ecom_slug : null), ['class' => 'form-control', 'placeholder' => 'product-url-slug']); !!}
        </div>
    </div>
    <div class="col-sm-4 ecom-only-fields">
        <div class="form-group">
            {!! Form::label('ecom_online_price', __('ecommerce::lang.online_price') . ':') !!}
            {!! Form::text('ecom_online_price', !empty($product) ? $product->ecom_online_price : null, ['class' => 'form-control input_number']); !!}
        </div>
    </div>
    <div class="col-sm-4 ecom-only-fields">
        <div class="form-group">
            {!! Form::label('ecom_discount_price', __('ecommerce::lang.discount_price') . ':') !!}
            {!! Form::text('ecom_discount_price', !empty($product) ? $product->ecom_discount_price : null, ['class' => 'form-control input_number']); !!}
        </div>
    </div>
    <div class="col-sm-4 ecom-only-fields">
        <div class="form-group">
            <br>
            <label>
                {!! Form::checkbox('ecom_is_featured', 1, !empty($product) ? $product->ecom_is_featured : false, ['class' => 'input-icheck']); !!}
                <strong>@lang('ecommerce::lang.featured')</strong>
            </label>
        </div>
    </div>
    <div class="col-sm-4 ecom-only-fields">
        <div class="form-group">
            <br>
            <label>
                {!! Form::checkbox('ecom_is_new_arrival', 1, !empty($product) ? $product->ecom_is_new_arrival : false, ['class' => 'input-icheck']); !!}
                <strong>@lang('ecommerce::lang.new_arrival')</strong>
            </label>
        </div>
    </div>
    <div class="col-sm-6 ecom-only-fields">
        <div class="form-group">
            {!! Form::label('ecom_short_description', __('ecommerce::lang.short_description') . ':') !!}
            {!! Form::textarea('ecom_short_description', !empty($product) ? $product->ecom_short_description : null, ['class' => 'form-control', 'rows' => 2]); !!}
        </div>
    </div>
    <div class="col-sm-6 ecom-only-fields">
        <div class="form-group">
            {!! Form::label('ecom_long_description', __('ecommerce::lang.long_description') . ':') !!}
            {!! Form::textarea('ecom_long_description', !empty($product) ? $product->ecom_long_description : null, ['class' => 'form-control', 'rows' => 2]); !!}
        </div>
    </div>
    <div class="col-sm-6 ecom-only-fields">
        <div class="form-group">
            {!! Form::label('ecom_seo_title', __('ecommerce::lang.seo_title') . ':') !!}
            {!! Form::text('ecom_seo_title', !empty($product) ? $product->ecom_seo_title : null, ['class' => 'form-control']); !!}
        </div>
    </div>
    <div class="col-sm-6 ecom-only-fields">
        <div class="form-group">
            {!! Form::label('ecom_seo_description', __('ecommerce::lang.seo_description') . ':') !!}
            {!! Form::textarea('ecom_seo_description', !empty($product) ? $product->ecom_seo_description : null, ['class' => 'form-control', 'rows' => 2]); !!}
        </div>
    </div>
    <div class="col-sm-6 ecom-only-fields">
        <div class="form-group">
            {!! Form::label('ecom_gallery', __('ecommerce::lang.gallery') . ':') !!}
            {!! Form::file('ecom_gallery[]', ['class' => 'form-control', 'multiple' => true, 'accept' => 'image/*']); !!}
        </div>
    </div>
</div>
<script>
(function() {
    function toggleEcomFields() {
        var v = document.getElementById('sales_channel');
        if (!v) return;
        var show = v.value !== 'pos_only';
        document.querySelectorAll('.ecom-only-fields').forEach(function(el) {
            el.style.display = show ? '' : 'none';
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        var v = document.getElementById('sales_channel');
        if (v) {
            v.addEventListener('change', toggleEcomFields);
            $(v).on('change', toggleEcomFields);
            toggleEcomFields();
        }
    });
})();
</script>
