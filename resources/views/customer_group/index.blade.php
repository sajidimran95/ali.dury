@extends('layouts.app')
@section('title', __('lang_v1.customer_groups'))

@section('css')
    <link rel="stylesheet" href="{{ asset('css/product-list-metrics.css?v=' . ($asset_v ?? '') . '&plm=4') }}">
@endsection

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header product-list-header">
        <div class="product-list-header__row">
            <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black product-list-header__title">
                @lang('lang_v1.customer_groups')
            </h1>
            <div class="product-list-header__actions">
                @can('customer.create')
                    <a class="product-list-header__btn product-list-header__btn--primary btn-modal"
                        data-href="{{ action([\App\Http\Controllers\CustomerGroupController::class, 'create']) }}"
                        data-container=".customer_groups_modal"
                        href="javascript:void(0);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 5l0 14" />
                            <path d="M5 12l14 0" />
                        </svg>
                        @lang('messages.add')
                    </a>
                @endcan
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        @if (!empty($customer_group_stats))
            @include('customer_group.partials.list_metrics')
        @endif

        @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.all_your_customer_groups')])
            @can('customer.view')
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="customer_groups_table">
                        <thead>
                            <tr>
                                <th>@lang('lang_v1.customer_group_name')</th>
                                <th>@lang('lang_v1.customers_count')</th>
                                <th>@lang('lang_v1.calculation_percentage')</th>
                                <th>@lang('lang_v1.selling_price_group')</th>
                                <th class="not-export">@lang('messages.action')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcan
        @endcomponent

        <div class="modal fade customer_groups_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->
@stop
@section('javascript')

    <script type="text/javascript">
        $(document).on('change', '#price_calculation_type', function() {
            var price_calculation_type = $(this).val();
            if (price_calculation_type == 'percentage') {
                $('.selling_price_group-field').addClass('hide');
            } else {
                $('.selling_price_group-field').removeClass('hide');
            }
            $('.percentage-field').removeClass('hide');
        })
    </script>
@endsection
