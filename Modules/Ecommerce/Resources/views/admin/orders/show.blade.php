@extends('layouts.app')
@section('title', __('ecommerce::lang.order_details').' - '.$order->invoice_no)

@section('content')
@php
    $contact = $order->contact;
    $shipping = $meta->shipping_address ?? [];
    $billing = $meta->billing_address ?? [];
    $fulfillment = $meta->fulfillment_status ?? 'pending';
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
    $total_paid = 0;
@endphp

<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        @lang('ecommerce::lang.order_details')
        <small class="tw-text-gray-600 tw-font-normal">#{{ $order->invoice_no }}</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('ecommerce.admin.orders') }}">@lang('ecommerce::lang.orders')</a></li>
        <li class="active">{{ $order->invoice_no }}</li>
    </ol>
</section>

<section class="content no-print">
    <div class="row">
        <div class="col-md-12 tw-mb-3">
            <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-justify-between">
                <a href="{{ route('ecommerce.admin.orders') }}" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-neutral tw-dw-btn-sm">
                    <i class="fas fa-arrow-left"></i> @lang('ecommerce::lang.back_to_orders')
                </a>
                <div class="tw-flex tw-flex-wrap tw-gap-2">
                    @if($order->type === 'sales_order' && in_array($order->status, ['ordered', 'partial'], true) && (auth()->user()->can('sell.create') || auth()->user()->can('direct_sell.access')))
                        <a href="{{ action([\App\Http\Controllers\SellController::class, 'create']) }}?sales_order_id={{ $order->id }}"
                           class="tw-dw-btn tw-dw-btn-warning tw-text-white tw-dw-btn-sm"
                           title="@lang('ecommerce::lang.convert_to_sale_help')">
                            <i class="fas fa-file-invoice"></i> @lang('ecommerce::lang.convert_to_sale')
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
                    @if($order->type === 'sales_order' && auth()->user()->can('so.update'))
                        <a href="{{ action([\App\Http\Controllers\SellController::class, 'edit'], [$order->id]) }}"
                           class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-info tw-dw-btn-sm">
                            <i class="fas fa-edit"></i> @lang('ecommerce::lang.edit_sales_order')
                        </a>
                    @endif
                    <a href="#" class="print-invoice tw-dw-btn tw-dw-btn-success tw-text-white tw-dw-btn-sm"
                       data-href="{{ route('sell.printInvoice', [$order->id]) }}?package_slip=true">
                        <i class="fas fa-file-alt"></i> @lang('lang_v1.packing_slip')
                    </a>
                    @can('print_invoice')
                        <a href="#" class="print-invoice tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm"
                           data-href="{{ route('sell.printInvoice', [$order->id]) }}">
                            <i class="fa fa-print"></i> @lang('lang_v1.print_invoice')
                        </a>
                        <a href="{{ route('ecommerce.admin.orders.pdf', $order->id) }}" target="_blank"
                           class="tw-dw-btn tw-dw-btn-error tw-text-white tw-dw-btn-sm">
                            <i class="fas fa-file-pdf"></i> @lang('lang_v1.download_pdf')
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('sale.sell_details')])
                <div class="row">
                    <div class="col-sm-4">
                        <p class="tw-mb-1"><b>@lang('sale.invoice_no'):</b> #{{ $order->invoice_no }}</p>
                        @if($order->type === 'sales_order')
                            <p class="tw-mb-1">
                                <b>@lang('lang_v1.sales_order'):</b>
                                <span class="label bg-info">{{ ucfirst($order->status) }}</span>
                            </p>
                            <p class="tw-mb-1">{!! $converted_invoice_html !!}</p>
                        @endif
                        <p class="tw-mb-1"><b>@lang('messages.date'):</b> {{ @format_datetime($order->transaction_date) }}</p>
                        <p class="tw-mb-1">
                            <b>@lang('sale.payment_status'):</b>
                            @if($order->payment_status)
                                <span class="label @if($order->payment_status == 'paid') bg-green @elseif($order->payment_status == 'due') bg-red @else bg-yellow @endif">
                                    {{ __('lang_v1.'.$order->payment_status) }}
                                </span>
                            @endif
                        </p>
                        <p class="tw-mb-1">
                            <b>@lang('ecommerce::lang.fulfillment'):</b>
                            <span class="label {{ $fulfillment_colors[$fulfillment] ?? 'bg-gray' }}">{{ ucfirst($fulfillment) }}</span>
                        </p>
                        @if(!empty($meta->payment_method))
                            <p class="tw-mb-1"><b>@lang('ecommerce::lang.payment_method'):</b> {{ strtoupper($meta->payment_method) }}</p>
                        @endif
                    </div>

                    <div class="col-sm-4">
                        <h4 class="tw-mt-0 tw-text-base tw-font-semibold">@lang('ecommerce::lang.customer_info')</h4>
                        @if(!empty($contact->supplier_business_name))
                            <p class="tw-mb-1"><b>{{ $contact->supplier_business_name }}</b></p>
                        @endif
                        <p class="tw-mb-1">{{ $contact->name ?? '-' }}</p>
                        @if(!empty($meta->customer_email) || !empty($contact->email))
                            <p class="tw-mb-1"><i class="fas fa-envelope tw-mr-1"></i> {{ $meta->customer_email ?? $contact->email }}</p>
                        @endif
                        @if(!empty($meta->customer_phone) || !empty($contact->mobile))
                            <p class="tw-mb-1"><i class="fas fa-phone tw-mr-1"></i> {{ $meta->customer_phone ?? $contact->mobile }}</p>
                        @endif
                    </div>

                    <div class="col-sm-4">
                        <h4 class="tw-mt-0 tw-text-base tw-font-semibold">@lang('ecommerce::lang.shipping_info')</h4>
                        @if(!empty($shipping))
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
                        @if($order->shipping_status)
                            <p class="tw-mb-1">
                                <b>@lang('lang_v1.shipping_status'):</b>
                                {{ $shipping_statuses[$order->shipping_status] ?? ucfirst($order->shipping_status) }}
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
                                <th class="text-right">@lang('sale.unit_price')</th>
                                <th class="text-right">@lang('sale.subtotal')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->sell_lines as $line)
                                @php
                                    $line_total = $line->quantity * $line->unit_price_inc_tax;
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
                                        <span class="display_currency" data-currency_symbol="true">{{ $line->unit_price_inc_tax }}</span>
                                    </td>
                                    <td class="text-right">
                                        <span class="display_currency" data-currency_symbol="true">{{ $line_total }}</span>
                                    </td>
                                </tr>
                            @endforeach
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
                @component('components.widget', ['class' => 'box-solid', 'title' => __('ecommerce::lang.order_note')])
                    <p class="well well-sm no-shadow bg-gray tw-mb-0">{!! nl2br(e($order->additional_notes)) !!}</p>
                @endcomponent
            @endif
        </div>

        <div class="col-md-4">
            @component('components.widget', ['class' => 'box-warning', 'title' => __('ecommerce::lang.fulfillment_status')])
                {!! Form::open(['url' => route('ecommerce.admin.orders.status', $order->id), 'method' => 'post']) !!}
                    <div class="form-group">
                        <label>@lang('ecommerce::lang.fulfillment_status')</label>
                        <select name="fulfillment_status" class="form-control">
                            @foreach(['pending','confirmed','processing','packed','shipped','delivered','cancelled','returned'] as $st)
                                <option value="{{ $st }}" @selected($fulfillment === $st)>{{ ucfirst($st) }}</option>
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
            @endcomponent

            @if($meta)
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
                    @if(!empty($meta->timeline))
                        <ul class="timeline tw-pl-0">
                            @foreach(array_reverse($meta->timeline) as $step)
                                <li class="tw-mb-3 tw-border-l-2 tw-border-gray-200 tw-pl-3">
                                    <strong>{{ $step['label'] ?? ucfirst($step['status'] ?? '') }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $step['at'] ?? '' }}</small>
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

            @if(!empty($billing) && $billing !== $shipping)
                @component('components.widget', ['class' => 'box-solid', 'title' => __('ecommerce::lang.billing_info')])
                    <p class="tw-mb-1">
                        {{ trim(($billing['first_name'] ?? '').' '.($billing['last_name'] ?? '')) ?: '-' }}
                    </p>
                    <p class="tw-mb-0">
                        {{ implode(', ', array_filter([
                            $billing['address_line_1'] ?? null,
                            $billing['address_line_2'] ?? null,
                            $billing['city'] ?? null,
                            trim(($billing['state'] ?? '').' '.($billing['zip_code'] ?? '')),
                            $billing['country'] ?? null,
                        ])) ?: '-' }}
                    </p>
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
    });
</script>
@endsection
