@extends('layouts.app')
@section('title', __('lang_v1.sales_order_details').' - '.$order->invoice_no)

@section('content')
@php
    $contact = $order->contact;
    $fulfillment = $meta->fulfillment_status ?? null;
    $fulfillment_colors = [
        'pending' => 'bg-yellow',
        'confirmed' => 'bg-aqua',
        'processing' => 'bg-light-blue',
        'packed' => 'bg-blue',
        'shipped' => 'bg-purple',
        'delivered' => 'bg-green',
        'cancelled' => 'bg-red',
        'returned' => 'bg-maroon',
    ];
    $pipeline_source = $order->order_source ?: $order->source;
    $in_fulfillment_pipeline = in_array($pipeline_source, \App\Services\FulfillmentService::PIPELINE_SOURCES, true);
    $ff_stage = $order->fulfillment_stage ?: ($in_fulfillment_pipeline ? 'picking' : null);
    $ff_stage_labels = [
        'picking' => __('fulfillment.picking'),
        'packing' => __('fulfillment.packing'),
        'shipment' => __('fulfillment.shipment'),
        'shipped' => __('fulfillment.shipped'),
    ];
    $so_status_meta = $sales_order_statuses[$order->status] ?? ['label' => ucfirst($order->status), 'class' => 'bg-gray'];
    $total_paid = 0;
@endphp

<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        @lang('lang_v1.sales_order_details')
        <small class="tw-text-gray-600 tw-font-normal">#{{ $order->invoice_no }}</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ action([\App\Http\Controllers\SalesOrderController::class, 'index']) }}">@lang('lang_v1.sales_orders')</a></li>
        <li class="active">{{ $order->invoice_no }}</li>
    </ol>
</section>

<section class="content no-print">
    <div class="row">
        <div class="col-md-12 tw-mb-3">
            <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-justify-between">
                <a href="{{ action([\App\Http\Controllers\SalesOrderController::class, 'index']) }}"
                   class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-neutral tw-dw-btn-sm">
                    <i class="fas fa-arrow-left"></i> @lang('lang_v1.back_to_sales_orders')
                </a>
                <div class="tw-flex tw-flex-wrap tw-gap-2">
                    @if(in_array($order->status, ['ordered', 'partial'], true) && (auth()->user()->can('sell.create') || auth()->user()->can('direct_sell.access')))
                        <a href="{{ action([\App\Http\Controllers\SellController::class, 'create']) }}?sales_order_id={{ $order->id }}"
                           class="tw-dw-btn tw-dw-btn-warning tw-text-white tw-dw-btn-sm">
                            <i class="fas fa-file-invoice"></i> @lang('lang_v1.convert_to_invoice')
                        </a>
                    @endif
                    @foreach($converted_invoices ?? [] as $converted_invoice)
                        @php
                            $converted_url = !empty($converted_invoice->is_direct_sale)
                                ? action([\App\Http\Controllers\SellController::class, 'edit'], [$converted_invoice->id])
                                : route('pos.edit', ['po' => $converted_invoice->id]);
                        @endphp
                        <a href="{{ $converted_url }}" target="_blank" class="tw-dw-btn tw-dw-btn-success tw-text-white tw-dw-btn-sm">
                            <i class="fas fa-external-link-alt"></i> @lang('sale.invoice_no') {{ $converted_invoice->invoice_no }}
                        </a>
                    @endforeach
                    @if($can_edit_order)
                        <a href="{{ action([\App\Http\Controllers\SellController::class, 'edit'], [$order->id]) }}"
                           class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-info tw-dw-btn-sm">
                            <i class="fas fa-edit"></i> @lang('messages.edit')
                        </a>
                    @endif
                    <a href="#" class="print-invoice tw-dw-btn tw-dw-btn-success tw-text-white tw-dw-btn-sm"
                       data-href="{{ route('sell.printInvoice', [$order->id]) }}?package_slip=true">
                        <i class="fas fa-file-alt"></i> @lang('lang_v1.packing_slip')
                    </a>
                    <a href="#" class="print-invoice tw-dw-btn tw-dw-btn-outline tw-dw-btn-success tw-dw-btn-sm"
                       data-href="{{ route('sell.printInvoice', [$order->id]) }}?delivery_note=true">
                        <i class="fas fa-truck"></i> @lang('lang_v1.delivery_note')
                    </a>
                    @can('print_invoice')
                        <a href="#" class="print-invoice tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm"
                           data-href="{{ route('sell.printInvoice', [$order->id]) }}">
                            <i class="fa fa-print"></i> @lang('lang_v1.print_invoice')
                        </a>
                    @endcan
                    @if($in_fulfillment_pipeline && \Illuminate\Support\Facades\Route::has('fulfillment.show'))
                        <a href="{{ route('fulfillment.show', [$order->id]) }}"
                           class="tw-dw-btn tw-dw-btn-warning tw-text-white tw-dw-btn-sm">
                            <i class="fas fa-boxes"></i>
                            @lang('fulfillment.breadcrumb_fulfillment')
                            @if($ff_stage)
                                ({{ $ff_stage_labels[$ff_stage] ?? ucfirst($ff_stage) }})
                            @endif
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('sale.sell_details')])
                <div class="row">
                    <div class="col-sm-4">
                        <p class="tw-mb-1"><b>@lang('restaurant.order_no'):</b> #{{ $order->invoice_no }}</p>
                        <p class="tw-mb-1">
                            <b>@lang('sale.status'):</b>
                            <span class="label {{ $so_status_meta['class'] }}">{{ $so_status_meta['label'] }}</span>
                        </p>
                        <p class="tw-mb-1">{!! $converted_invoice_html !!}</p>
                        <p class="tw-mb-1"><b>@lang('messages.date'):</b> {{ @format_datetime($order->transaction_date) }}</p>
                        @if($order->shipping_status)
                            <p class="tw-mb-1">
                                <b>@lang('lang_v1.shipping_status'):</b>
                                {{ $shipping_statuses[$order->shipping_status] ?? ucfirst($order->shipping_status) }}
                            </p>
                        @endif
                        @if(!empty($fulfillment))
                            <p class="tw-mb-1">
                                <b>@lang('ecommerce::lang.fulfillment'):</b>
                                <span class="label {{ $fulfillment_colors[$fulfillment] ?? 'bg-gray' }}">{{ ucfirst($fulfillment) }}</span>
                            </p>
                        @elseif($in_fulfillment_pipeline && $ff_stage)
                            <p class="tw-mb-1">
                                <b>@lang('fulfillment.breadcrumb_fulfillment'):</b>
                                <span class="label bg-aqua">{{ $ff_stage_labels[$ff_stage] ?? ucfirst($ff_stage) }}</span>
                            </p>
                        @endif
                        @php
                            $order_source = \App\Transaction::orderSourceMeta(
                                !empty($order->source) ? $order->source : ((!empty($meta) ? 'ecommerce' : 'web'))
                            );
                        @endphp
                        <p class="tw-mb-1">
                            <b>@lang('lang_v1.source'):</b>
                            <span class="label {{ $order_source['class'] }}">{{ $order_source['label'] }}</span>
                        </p>
                    </div>

                    <div class="col-sm-4">
                        <h4 class="tw-mt-0 tw-text-base tw-font-semibold">@lang('contact.customer')</h4>
                        @if(!empty($contact->supplier_business_name))
                            <p class="tw-mb-1"><b>{{ $contact->supplier_business_name }}</b></p>
                        @endif
                        <p class="tw-mb-1">{{ $contact->name ?? '-' }}</p>
                        @if(!empty($contact->email))
                            <p class="tw-mb-1"><i class="fas fa-envelope tw-mr-1"></i> {{ $contact->email }}</p>
                        @endif
                        @if(!empty($contact->mobile))
                            <p class="tw-mb-1"><i class="fas fa-phone tw-mr-1"></i> {{ $contact->mobile }}</p>
                        @endif
                        @if(!empty($meta->customer_email) && $meta->customer_email !== ($contact->email ?? null))
                            <p class="tw-mb-1"><i class="fas fa-envelope tw-mr-1"></i> {{ $meta->customer_email }}</p>
                        @endif
                        @if(!empty($meta->customer_phone) && $meta->customer_phone !== ($contact->mobile ?? null))
                            <p class="tw-mb-1"><i class="fas fa-phone tw-mr-1"></i> {{ $meta->customer_phone }}</p>
                        @endif
                    </div>

                    <div class="col-sm-4">
                        <h4 class="tw-mt-0 tw-text-base tw-font-semibold">@lang('lang_v1.shipping_address')</h4>
                        @php
                            $shipping = $meta->shipping_address ?? [];
                        @endphp
                        @if(!empty($shipping) && is_array($shipping))
                            <p class="tw-mb-1">
                                {{ trim(($shipping['first_name'] ?? '').' '.($shipping['last_name'] ?? '')) ?: ($order->delivered_to ?: '-') }}
                            </p>
                            <p class="tw-mb-1">
                                {{ implode(', ', array_filter([
                                    $shipping['address_line_1'] ?? null,
                                    $shipping['address_line_2'] ?? null,
                                    $shipping['city'] ?? null,
                                    trim(($shipping['state'] ?? '').' '.($shipping['zip_code'] ?? '')),
                                    $shipping['country'] ?? null,
                                ])) ?: ($order->shipping_address ?: '-') }}
                            </p>
                        @else
                            <p class="tw-mb-1">{{ $order->shipping_address ?: '-' }}</p>
                            @if($order->delivered_to)
                                <p class="tw-mb-1"><b>@lang('lang_v1.delivered_to'):</b> {{ $order->delivered_to }}</p>
                            @endif
                        @endif
                        @if(!empty($order->delivery_person_user))
                            <p class="tw-mb-1">
                                <b>@lang('lang_v1.delivery_person'):</b>
                                {{ $order->delivery_person_user->user_full_name }}
                            </p>
                        @endif
                    </div>
                </div>
            @endcomponent

            @component('components.widget', ['class' => 'box-primary', 'title' => __('sale.products')])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>@lang('sale.product')</th>
                                <th class="text-right">@lang('sale.qty')</th>
                                <th class="text-right">@lang('lang_v1.quantity_remaining')</th>
                                <th class="text-right">@lang('sale.unit_price')</th>
                                <th class="text-right">@lang('sale.subtotal')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->sell_lines as $line)
                                @php
                                    $line_total = $line->quantity * $line->unit_price_inc_tax;
                                    $invoiced = (float) ($line->so_quantity_invoiced ?? 0);
                                    $remaining = (float) $line->quantity - $invoiced;
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        {{ optional($line->product)->name }}
                                        @if(optional($line->product)->type === 'variable')
                                            - {{ optional(optional($line->variations)->product_variation)->name }}
                                            - {{ optional($line->variations)->name }}
                                        @endif
                                        @if(!empty(optional($line->variations)->sub_sku))
                                            <br><small class="text-muted">{{ $line->variations->sub_sku }}</small>
                                        @endif
                                        @if(!empty($line->sell_line_note))
                                            <br><small>{{ $line->sell_line_note }}</small>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <span class="display_currency" data-currency_symbol="false" data-is_quantity="true">{{ $line->quantity }}</span>
                                        {{ optional(optional($line->product)->unit)->short_name }}
                                    </td>
                                    <td class="text-right">
                                        <span class="display_currency" data-currency_symbol="false" data-is_quantity="true">{{ $remaining }}</span>
                                    </td>
                                    <td class="text-right">
                                        <span class="display_currency" data-currency_symbol="true">{{ $line->unit_price_inc_tax }}</span>
                                    </td>
                                    <td class="text-right">
                                        <span class="display_currency" data-currency_symbol="true">{{ $line_total }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">@lang('lang_v1.no_order_items')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="row">
                    <div class="col-md-6 col-md-offset-6">
                        <table class="table">
                            <tr>
                                <th>@lang('sale.total'):</th>
                                <td class="text-right">
                                    <span class="display_currency" data-currency_symbol="true">{{ $order->total_before_tax }}</span>
                                </td>
                            </tr>
                            @if($order->discount_amount > 0)
                                <tr>
                                    <th>@lang('sale.discount'):</th>
                                    <td class="text-right">
                                        (-)
                                        <span class="display_currency" @if($order->discount_type == 'fixed') data-currency_symbol="true" @endif>
                                            {{ $order->discount_amount }}
                                        </span>
                                        @if($order->discount_type == 'percentage')% @endif
                                    </td>
                                </tr>
                            @endif
                            @if($order->tax_amount > 0)
                                <tr>
                                    <th>@lang('sale.order_tax'):</th>
                                    <td class="text-right">
                                        (+) <span class="display_currency" data-currency_symbol="true">{{ $order->tax_amount }}</span>
                                    </td>
                                </tr>
                            @endif
                            @if($order->shipping_charges > 0)
                                <tr>
                                    <th>@lang('sale.shipping'):</th>
                                    <td class="text-right">
                                        (+) <span class="display_currency" data-currency_symbol="true">{{ $order->shipping_charges }}</span>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <th>@lang('sale.total_payable'):</th>
                                <td class="text-right">
                                    <strong><span class="display_currency" data-currency_symbol="true">{{ $order->final_total }}</span></strong>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            @endcomponent

            @if($order->payment_lines->count())
                @component('components.widget', ['class' => 'box-primary', 'title' => __('sale.payment_info')])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>@lang('messages.date')</th>
                                    <th>@lang('purchase.ref_no')</th>
                                    <th>@lang('sale.amount')</th>
                                    <th>@lang('sale.payment_mode')</th>
                                    <th>@lang('sale.payment_note')</th>
                                    <th>Signature</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->payment_lines as $payment_line)
                                    @php
                                        if ($payment_line->is_return == 1) {
                                            $total_paid -= $payment_line->amount;
                                        } else {
                                            $total_paid += $payment_line->amount;
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ @format_date($payment_line->paid_on) }}</td>
                                        <td>{{ $payment_line->payment_ref_no }}</td>
                                        <td><span class="display_currency" data-currency_symbol="true">{{ $payment_line->amount }}</span></td>
                                        <td>{{ $payment_types[$payment_line->method] ?? $payment_line->method }}</td>
                                        <td>{{ $payment_line->note ? ucfirst($payment_line->note) : '--' }}</td>
                                        <td>@include('transaction_payment.partials.signature', ['payment' => $payment_line])</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right">@lang('sale.total_paid')</th>
                                    <th colspan="3"><span class="display_currency" data-currency_symbol="true">{{ $total_paid }}</span></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endcomponent
            @endif

            @if($order->additional_notes)
                @component('components.widget', ['class' => 'box-solid', 'title' => __('lang_v1.additional_notes')])
                    <p class="well well-sm no-shadow bg-gray tw-mb-0">{!! nl2br(e($order->additional_notes)) !!}</p>
                @endcomponent
            @endif
        </div>

        <div class="col-md-4">
            @if($can_update_status)
                @component('components.widget', ['class' => 'box-warning', 'title' => __('lang_v1.manage_order_status')])
                    {!! Form::open(['url' => route('sales-order.status', $order->id), 'method' => 'post']) !!}
                        <div class="form-group">
                            <label>@lang('sale.status')</label>
                            <select name="status" class="form-control">
                                @foreach($sales_order_statuses as $key => $so_status)
                                    <option value="{{ $key }}" @selected($order->status === $key)>{{ $so_status['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-w-full">
                            <i class="fas fa-save"></i> @lang('messages.update')
                        </button>
                    {!! Form::close() !!}
                @endcomponent
            @endif

            @if($can_update_shipping)
                @component('components.widget', ['class' => 'box-info', 'title' => __('lang_v1.manage_shipping')])
                    {!! Form::open(['url' => route('sales-order.shipping', $order->id), 'method' => 'post']) !!}
                        <div class="form-group">
                            {!! Form::label('shipping_status', __('lang_v1.shipping_status')) !!}
                            {!! Form::select('shipping_status', $shipping_statuses, $order->shipping_status, ['class' => 'form-control', 'placeholder' => __('messages.please_select')]) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('delivered_to', __('lang_v1.delivered_to')) !!}
                            {!! Form::text('delivered_to', $order->delivered_to, ['class' => 'form-control', 'placeholder' => __('lang_v1.delivered_to')]) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('delivery_person', __('lang_v1.delivery_person')) !!}
                            {!! Form::select('delivery_person', $users, $order->delivery_person, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'style' => 'width:100%']) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('shipping_address', __('lang_v1.shipping_address')) !!}
                            {!! Form::textarea('shipping_address', $order->shipping_address, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('lang_v1.shipping_address')]) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('shipping_details', __('sale.shipping_details')) !!}
                            {!! Form::textarea('shipping_details', $order->shipping_details, ['class' => 'form-control', 'rows' => 2, 'placeholder' => __('sale.shipping_details')]) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('shipping_note', __('lang_v1.shipping_note')) !!}
                            {!! Form::text('shipping_note', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.optional')]) !!}
                        </div>
                        <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-w-full">
                            <i class="fas fa-truck"></i> @lang('messages.update')
                        </button>
                    {!! Form::close() !!}
                @endcomponent
            @endif

            {{-- Same fulfillment panel for Online / Sales App / Customer App --}}
            @if($meta && ($can_update_status || $in_fulfillment_pipeline))
                @component('components.widget', ['class' => 'box-warning', 'title' => __('ecommerce::lang.fulfillment_status')])
                    @if($in_fulfillment_pipeline && $ff_stage)
                        <p class="tw-mb-2">
                            <b>@lang('fulfillment.breadcrumb_fulfillment'):</b>
                            <span class="label bg-aqua">{{ $ff_stage_labels[$ff_stage] ?? ucfirst((string) $ff_stage) }}</span>
                        </p>
                    @endif
                    @if($can_update_status)
                        {!! Form::open(['url' => route('sales-order.fulfillment', $order->id), 'method' => 'post']) !!}
                            <div class="form-group">
                                <label>@lang('ecommerce::lang.fulfillment_status')</label>
                                <select name="fulfillment_status" class="form-control">
                                    @foreach(['pending','confirmed','processing','packed','shipped','delivered','cancelled','returned'] as $st)
                                        <option value="{{ $st }}" @selected(($fulfillment ?? 'pending') === $st)>{{ ucfirst($st) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>@lang('brand.note')</label>
                                <input type="text" name="note" class="form-control" placeholder="@lang('lang_v1.optional')">
                            </div>
                            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-w-full">
                                <i class="fas fa-save"></i> @lang('messages.update')
                            </button>
                        {!! Form::close() !!}
                    @endif
                    @if($in_fulfillment_pipeline && \Illuminate\Support\Facades\Route::has('fulfillment.show'))
                        <a href="{{ route('fulfillment.show', [$order->id]) }}"
                           class="tw-dw-btn tw-dw-btn-warning tw-text-white tw-w-full tw-mt-2">
                            <i class="fas fa-boxes"></i>
                            {{ __('fulfillment.open') }}
                            @if($ff_stage)
                                — {{ $ff_stage_labels[$ff_stage] ?? ucfirst((string) $ff_stage) }}
                            @endif
                        </a>
                        <div class="tw-mt-2 tw-flex tw-flex-wrap tw-gap-1">
                            @foreach(['picking' => 'fulfillment.picking', 'packing' => 'fulfillment.packing', 'shipment' => 'fulfillment.shipment'] as $stageKey => $stageLang)
                                @if(\Illuminate\Support\Facades\Route::has('fulfillment.'.$stageKey))
                                    <a href="{{ route('fulfillment.'.$stageKey) }}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-neutral">
                                        @lang($stageLang)
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                @endcomponent

                @component('components.widget', ['class' => 'box-info', 'title' => __('ecommerce::lang.tracking_token')])
                    <p class="tw-mb-2"><code class="tw-break-all">{{ $meta->tracking_token }}</code></p>
                    @if(!empty($meta->tracking_token))
                        <a href="{{ url(config('ecommerce.route_prefix', 'shop').'/track?token='.$meta->tracking_token) }}"
                           target="_blank" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info">
                            <i class="fas fa-external-link-alt"></i> @lang('messages.view')
                        </a>
                    @endif
                @endcomponent

                @component('components.widget', ['class' => 'box-solid', 'title' => __('ecommerce::lang.timeline')])
                    @php
                        $ff_timeline = $meta->timeline ?? [];
                        $wh_timeline = ($order->fulfillment_meta['timeline'] ?? []) ?: [];
                        $merged_timeline = collect(array_merge($ff_timeline, $wh_timeline))
                            ->sortByDesc(fn ($s) => $s['at'] ?? '')
                            ->values()
                            ->all();
                    @endphp
                    @if(!empty($merged_timeline))
                        <ul class="timeline tw-pl-0">
                            @foreach($merged_timeline as $step)
                                <li class="tw-mb-3 tw-border-l-2 tw-border-gray-200 tw-pl-3">
                                    <strong>{{ $step['label'] ?? ucfirst($step['status'] ?? $step['event'] ?? '') }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $step['at'] ?? '' }}@if(!empty($step['by'])) · {{ $step['by'] }}@endif</small>
                                    @if(!empty($step['note']))
                                        <br><small>{{ $step['note'] }}</small>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted tw-mb-0">—</p>
                    @endif
                @endcomponent
            @endif
        </div>
    </div>
</section>

<section class="invoice print_section" id="receipt_section"></section>
@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function () {
        __currency_convert_recursively($('section.content'));
        if ($('.select2').length) {
            $('.select2').select2();
        }
    });
</script>
@endsection
