@extends('layouts.app')
@section('title', __('ecommerce::lang.reports'))

@section('css')
    <link rel="stylesheet" href="{{ asset('css/product-list-metrics.css?v=' . ($asset_v ?? '') . '&plm=6') }}">
@endsection

@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('ecommerce::lang.reports')</h1>
</section>

<section class="content">
    @include('ecommerce::admin.reports.partials.list_metrics')

    <div class="box">
        <div class="box-header">
            <h3 class="box-title">@lang('ecommerce::lang.top_products')</h3>
        </div>
        <div class="box-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>@lang('sale.product')</th>
                        <th>@lang('lang_v1.total_items')</th>
                        <th>@lang('ecommerce::lang.revenue')</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($top_products as $row)
                    <tr>
                        <td>{{ $row->name }}</td>
                        <td>{{ @num_format($row->qty) }}</td>
                        <td>
                            <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $row->revenue }}">{{ $row->revenue }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">@lang('ecommerce::lang.no_product_sales')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script>
$(document).ready(function () {
    if (typeof __currency_convert_recursively === 'function') {
        __currency_convert_recursively($('#ecom_report_list_metrics'));
        __currency_convert_recursively($('.box-body'));
    }
});
</script>
@endsection
