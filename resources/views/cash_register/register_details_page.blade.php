@extends('layouts.app')
@section('title', __('cash_register.register_details'))

@section('css')
<link rel="stylesheet" href="{{ asset('css/pos-sale-theme.css?v=' . ($asset_v ?? '') . '&ls=55') }}">
@endsection

@section('content')
<script>document.body.classList.add('pos-cr-dark-page');</script>
<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        @lang('cash_register.register_details')
        <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">
            {{ $register_details->open_time ? \Carbon::createFromFormat('Y-m-d H:i:s', $register_details->open_time)->format('jS M, Y h:i A') : '-' }}
            -
            {{ $close_time ? \Carbon::createFromFormat('Y-m-d H:i:s', $close_time)->format('jS M, Y h:i A') : '-' }}
        </small>
    </h1>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-body">
            @include('cash_register.payment_details')
            <hr>
            @if(!empty($register_details->denominations))
                @php $total = 0; @endphp
                <div class="row">
                    <div class="col-md-8 col-sm-12">
                        <h3>@lang('lang_v1.cash_denominations')</h3>
                        <table class="table table-slim">
                            <thead>
                                <tr>
                                    <th width="20%" class="text-right">@lang('lang_v1.denomination')</th>
                                    <th width="20%">&nbsp;</th>
                                    <th width="20%" class="text-center">@lang('lang_v1.count')</th>
                                    <th width="20%">&nbsp;</th>
                                    <th width="20%" class="text-left">@lang('sale.subtotal')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($register_details->denominations as $key => $value)
                                    <tr>
                                        <td class="text-right">{{ $key }}</td>
                                        <td class="text-center">X</td>
                                        <td class="text-center">{{ $value ?? 0 }}</td>
                                        <td class="text-center">=</td>
                                        <td class="text-left">@format_currency($key * ($value ?? 0))</td>
                                    </tr>
                                    @php $total += ((float) $key) * ((float) ($value ?? 0)); @endphp
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-center">@lang('sale.total')</th>
                                    <td>@format_currency($total)</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-xs-6">
                    <b>@lang('report.user'):</b> {{ $register_details->user_name }}<br>
                    <b>@lang('business.email'):</b> {{ $register_details->email }}<br>
                    <b>@lang('business.business_location'):</b> {{ $register_details->location_name }}<br>
                </div>
                @if(!empty($register_details->closing_note))
                    <div class="col-xs-6">
                        <strong>@lang('cash_register.closing_note'):</strong><br>
                        {{ $register_details->closing_note }}
                    </div>
                @endif
            </div>
        </div>
        <div class="box-footer no-print">
            <a href="{{ action([\App\Http\Controllers\SellPosController::class, 'create']) }}" class="tw-dw-btn tw-dw-btn-neutral tw-text-white">
                @lang('lang_v1.go_back')
            </a>
            @can('close_cash_register')
                <a href="{{ action([\App\Http\Controllers\CashRegisterController::class, 'getCloseRegister']) }}" class="tw-dw-btn tw-dw-btn-primary tw-text-white">
                    @lang('cash_register.close_register')
                </a>
            @endcan
        </div>
    </div>
</section>
@endsection
