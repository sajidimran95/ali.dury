@extends('layouts.app')
@section('title', __('delivery.management'))

@section('content')
<section class="content-header">
    <h1>{{ __('delivery.management') }} <small>{{ __('delivery.assign_orders') }}</small></h1>
</section>
<section class="content">
    @if(session('status'))
        @php $st = session('status'); @endphp
        <div class="alert {{ !empty($st['success']) ? 'alert-success' : 'alert-danger' }}">{{ $st['msg'] ?? '' }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="box box-primary">
        <div class="box-header with-border">
            <form method="GET" class="form-inline" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <input type="date" name="date" value="{{ $date }}" class="form-control">
                <select name="filter" class="form-control">
                    <option value="unassigned" @selected($filter==='unassigned')>{{ __('delivery.unassigned') }}</option>
                    <option value="assigned" @selected($filter==='assigned')>{{ __('delivery.assigned') }}</option>
                    <option value="all" @selected($filter==='all')>{{ __('delivery.all') }}</option>
                </select>
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ __('lang_v1.search') }}">
                <button class="btn btn-default" type="submit">{{ __('messages.filter') }}</button>
                <a href="{{ route('deliveries.routes', ['date' => $date]) }}" class="btn btn-info">{{ __('delivery.todays_routes') }}</a>
            </form>
        </div>
        <div class="box-body">
            <form method="POST" action="{{ route('deliveries.assign.store') }}" id="dlvAssignForm">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <div class="row" style="margin-bottom:12px;">
                    <div class="col-md-4">
                        <label>{{ __('delivery.driver') }}</label>
                        <select name="driver_id" class="form-control" required>
                            <option value="">{{ __('messages.please_select') }}</option>
                            @foreach($drivers as $d)
                                <option value="{{ $d->id }}">{{ $d->user_full_name }} ({{ $d->username }})</option>
                            @endforeach
                        </select>
                        @if($drivers->isEmpty())
                            <p class="help-block text-danger">{{ __('delivery.no_drivers_hint') }}</p>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <label>{{ __('business.business_location') }}</label>
                        <select name="location_id" class="form-control">
                            <option value="">{{ __('messages.please_select') }}</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5" style="padding-top:24px;">
                        <button type="submit" name="generate" value="0" class="btn btn-primary">{{ __('delivery.assign_only') }}</button>
                        <button type="submit" name="generate" value="1" class="btn btn-success">{{ __('delivery.assign_and_generate') }}</button>
                    </div>
                </div>

                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="dlvCheckAll"></th>
                            <th>@lang('sale.invoice_no')</th>
                            <th>@lang('contact.customer')</th>
                            <th>@lang('lang_v1.shipping_address')</th>
                            <th>@lang('sale.total')</th>
                            <th>{{ __('delivery.driver') }}</th>
                            <th>{{ __('delivery.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td><input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="dlv-order-cb"></td>
                                <td>{{ $order->invoice_no }}</td>
                                <td>{{ optional($order->contact)->supplier_business_name ?: optional($order->contact)->name }}</td>
                                <td style="max-width:240px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $order->shipping_address }}">{{ $order->shipping_address ?: '—' }}</td>
                                <td>@format_currency($order->final_total)</td>
                                <td>{{ optional($order->delivery_person_user)->user_full_name ?: '—' }}</td>
                                <td>{{ $order->delivery_status ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center">{{ __('delivery.no_orders') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </form>
            {{ $orders->links() }}
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script>
$('#dlvCheckAll').on('change', function () {
    $('.dlv-order-cb').prop('checked', this.checked);
});
</script>
@endsection
