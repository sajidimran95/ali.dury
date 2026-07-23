@extends('layouts.app')
@section('title', __('crm::lang.settings'))
@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold">@lang('crm::lang.settings')</h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div class="box box-solid">
                <div class="box-header with-border"><h3 class="box-title">@lang('crm::lang.settings')</h3></div>
                <div class="box-body">
                    <form method="POST" action="{{ route('crm.settings.update') }}">
                        @csrf
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="enable_order_request" value="1" @checked(!empty($crm_settings['enable_order_request']))>
                                @lang('crm::lang.enable_order_request')
                            </label>
                            <p class="help-block">@lang('crm::lang.enable_order_request_help')</p>
                        </div>
                        <button type="submit" class="btn btn-primary">@lang('crm::lang.save')</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box box-solid">
                <div class="box-header with-border"><h3 class="box-title">@lang('crm::lang.sources') / @lang('crm::lang.life_stages')</h3></div>
                <div class="box-body">
                    <p>
                        <a href="{{ action([\App\Http\Controllers\TaxonomyController::class, 'index']) }}?type=crm_source" class="btn btn-default btn-block">
                            @lang('crm::lang.manage_sources')
                        </a>
                    </p>
                    <p>
                        <a href="{{ action([\App\Http\Controllers\TaxonomyController::class, 'index']) }}?type=crm_life_stage" class="btn btn-default btn-block">
                            @lang('crm::lang.manage_life_stages')
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
