@extends('layouts.app')
@section('title', __('lang_v1.sales_commission_agents'))

@section('css')
    <link rel="stylesheet" href="{{ asset('css/product-list-metrics.css?v=' . ($asset_v ?? '') . '&plm=7') }}">
@endsection

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header product-list-header">
    <div class="product-list-header__row">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black product-list-header__title">
            @lang('lang_v1.sales_commission_agents')
        </h1>
        <div class="product-list-header__actions">
            @can('user.create')
                <a class="product-list-header__btn product-list-header__btn--primary btn-modal"
                    data-href="{{ action([\App\Http\Controllers\SalesCommissionAgentController::class, 'create']) }}"
                    data-container=".commission_agent_modal"
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
    @if (!empty($agent_stats))
        @include('sales_commission_agent.partials.list_metrics')
    @endif

    @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.sales_commission_agents')])
        @can('user.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="sales_commission_agent_table">
                    <thead>
                        <tr>
                            <th>@lang('user.name')</th>
                            <th>@lang('business.email')</th>
                            <th>@lang('lang_v1.contact_no')</th>
                            <th>@lang('business.address')</th>
                            <th>@lang('lang_v1.cmmsn_percent')</th>
                            <th class="not-export">@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade commission_agent_modal" tabindex="-1" role="dialog"
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
