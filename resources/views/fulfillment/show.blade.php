@php
    $steps = [
        'picking' => ['n' => 1, 'label' => __('fulfillment.picking')],
        'packing' => ['n' => 2, 'label' => __('fulfillment.packing')],
        'shipment' => ['n' => 3, 'label' => __('fulfillment.shipment')],
    ];
    $order_of = ['picking' => 1, 'packing' => 2, 'shipment' => 3, 'shipped' => 4];
    $cur = $order_of[$stage] ?? 1;
    $started = !empty(($meta['timeline'] ?? [])[1]) || $stage !== 'picking';
@endphp
@extends('layouts.app')
@section('title', $order->invoice_no)

@section('css')
    <link rel="stylesheet" href="{{ asset('css/japs-fulfillment.css?v=' . $asset_v) }}">
@endsection

@section('content')
<div class="japs-dashboard-page">
<div class="ff-page">
    <p class="ff-crumb">{{ __('fulfillment.breadcrumb_orders') }} / {{ __('fulfillment.breadcrumb_fulfillment') }} / {{ __('fulfillment.'.$stage) }}</p>

    <div class="ff-title-row">
        <h1>Order #{{ $order->invoice_no }}</h1>
        <span class="ff-source-badge {{ $badge['class'] }}">{{ strtoupper($badge['short']) }}</span>
        <div class="ff-source-filter ff-source-filter--right">
            <span>{{ __('fulfillment.source') }}</span>
            <span class="ff-source-badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
        </div>
    </div>

    <div class="ff-stepper">
        @foreach ($steps as $key => $step)
            @php
                $done = $cur > $step['n'] || $stage === 'shipped';
                $active = $stage === $key;
            @endphp
            <div class="ff-step {{ $done ? 'is-done' : '' }} {{ $active ? 'is-active' : '' }}">
                <span class="ff-step__dot">{{ $done ? '✓' : $step['n'] }}</span>
                <span class="ff-step__lab">{{ $step['label'] }}</span>
                <span class="ff-step__sub">
                    @if ($done)
                        {{ __('fulfillment.completed') }}
                    @elseif ($active)
                        {{ __('fulfillment.in_progress') }}
                    @else
                        {{ __('fulfillment.upcoming') }}
                    @endif
                </span>
            </div>
            @if ($key !== 'shipment')
                <span class="ff-step__line {{ $done || $active ? 'is-on' : '' }}"></span>
            @endif
        @endforeach
    </div>

    <div class="ff-layout">
        <div class="ff-main">
            @if ($stage === 'picking')
                <div class="ff-grid-2">
                    <section class="ff-card">
                        <h3>{{ __('fulfillment.customer') }}</h3>
                        <p class="ff-strong">{{ $order->contact->name ?? '—' }}</p>
                        <p>{{ $order->contact->email ?? '' }}</p>
                        <p>{{ $order->contact->mobile ?? $order->contact->landline ?? '' }}</p>
                    </section>
                    <section class="ff-card">
                        <h3>{{ __('fulfillment.shipping_address') }}</h3>
                        <p class="ff-strong">{{ $order->delivered_to ?: ($order->contact->name ?? '') }}</p>
                        <p>{{ $order->shipping_address ?: $order->shipping_details }}</p>
                        <span class="ff-mini-badge">{{ __('fulfillment.residential') }}</span>
                    </section>
                </div>

                {!! Form::open(['url' => route('fulfillment.pick', $order->id), 'id' => 'ff_pick_form']) !!}
                <section class="ff-card">
                    <div class="ff-card__head">
                        <h3>{{ __('fulfillment.items_to_pick') }}</h3>
                        <span>{{ __('fulfillment.items_picked_of', ['picked' => $pick['picked'], 'total' => $pick['total']]) }}</span>
                    </div>
                    <table class="ff-items">
                        <thead>
                            <tr>
                                <th></th>
                                <th>@lang('sale.product')</th>
                                <th>SKU</th>
                                <th>{{ __('fulfillment.qty_ordered') }}</th>
                                <th>{{ __('fulfillment.qty_picked') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->sell_lines as $line)
                                @php $sku = $line->variations->sub_sku ?? $line->product->sku ?? ''; @endphp
                                <tr>
                                    <td><input type="checkbox" class="ff-pick-check" data-max="{{ (float) $line->quantity }}"></td>
                                    <td class="ff-item-cell">
                                        @if (!empty($line->product->image_url))
                                            <img src="{{ $line->product->image_url }}" alt="">
                                        @endif
                                        <span>{{ $line->product->name ?? '' }}
                                            @if (!empty($line->variations->name))
                                                <small>{{ $line->variations->name }}</small>
                                            @endif
                                        </span>
                                    </td>
                                    <td>{{ $sku }}</td>
                                    <td>{{ (float) $line->quantity }}</td>
                                    <td>
                                        <input type="number" min="0" step="0.01" max="{{ (float) $line->quantity }}"
                                            name="qty_picked[{{ $line->id }}]"
                                            class="form-control ff-qty-picked"
                                            value="{{ (float) ($line->qty_picked ?? 0) }}">
                                        <small>/ {{ (float) $line->quantity }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <input type="hidden" name="advance" id="ff_advance" value="0">
                    <button type="submit" class="btn btn-default" onclick="$('#ff_advance').val(0)">{{ __('fulfillment.save_picks') }}</button>
                    <button type="submit" class="btn btn-primary" id="ff_ready_pack" disabled onclick="$('#ff_advance').val(1)">{{ __('fulfillment.mark_ready_packing') }}</button>
                    <p class="ff-hint">{{ __('fulfillment.pick_hint') }}</p>
                </section>
                {!! Form::close() !!}
            @elseif ($stage === 'packing')
                {!! Form::open(['url' => route('fulfillment.pack', $order->id)]) !!}
                <section class="ff-card">
                    <h3>{{ __('fulfillment.pack_order') }}</h3>
                    <table class="ff-items">
                        <thead>
                            <tr>
                                <th>@lang('sale.product')</th>
                                <th>SKU</th>
                                <th>{{ __('fulfillment.qty_ordered') }}</th>
                                <th>{{ __('fulfillment.qty_picked') }}</th>
                                <th>{{ __('sale.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->sell_lines as $line)
                                <tr>
                                    <td class="ff-item-cell">
                                        @if (!empty($line->product->image_url))
                                            <img src="{{ $line->product->image_url }}" alt="">
                                        @endif
                                        {{ $line->product->name ?? '' }}
                                    </td>
                                    <td>{{ $line->variations->sub_sku ?? $line->product->sku ?? '' }}</td>
                                    <td>{{ (float) $line->quantity }}</td>
                                    <td>{{ (float) ($line->qty_picked ?? 0) }}</td>
                                    <td><span class="ff-pill is-ok">{{ __('fulfillment.picked') }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </section>
                <div class="ff-grid-2">
                    <section class="ff-card">
                        <h3>{{ __('fulfillment.packing_checklist') }}</h3>
                        @foreach (['items_verified', 'packaging_selected', 'packing_slip', 'package_sealed'] as $ck)
                            <label class="ff-check"><input type="checkbox" name="{{ $ck }}" value="1" @checked(!empty($checklist[$ck]))> {{ __('fulfillment.'.$ck) }}</label>
                        @endforeach
                    </section>
                    <section class="ff-card">
                        <h3>{{ __('fulfillment.package_details') }}</h3>
                        <p class="ff-hint">{{ __('fulfillment.package_hint') }}</p>
                        <label>{{ __('fulfillment.package_type') }}</label>
                        <select name="package_type" class="form-control">
                            <option value="">—</option>
                            <option value="box" @selected(($package['package_type'] ?? '') === 'box')>Box</option>
                            <option value="envelope" @selected(($package['package_type'] ?? '') === 'envelope')>Envelope</option>
                            <option value="poly" @selected(($package['package_type'] ?? '') === 'poly')>Poly mailer</option>
                        </select>
                        <label>{{ __('fulfillment.dimensions_in') }}</label>
                        <div class="ff-dims">
                            <input class="form-control" name="length" placeholder="{{ __('fulfillment.dim_l') }}" value="{{ $package['length'] ?? '' }}" inputmode="decimal">
                            <input class="form-control" name="width" placeholder="{{ __('fulfillment.dim_w') }}" value="{{ $package['width'] ?? '' }}" inputmode="decimal">
                            <input class="form-control" name="height" placeholder="{{ __('fulfillment.dim_h') }}" value="{{ $package['height'] ?? '' }}" inputmode="decimal">
                        </div>
                        <label>{{ __('fulfillment.weight_lb') }}</label>
                        <input class="form-control" name="weight" placeholder="e.g. 2.4" value="{{ $package['weight'] ?? '' }}" inputmode="decimal">
                        <p class="text-muted" style="margin-top:10px;font-size:12px;">{{ __('fulfillment.tracking_after_pack') }}</p>
                    </section>
                </div>
                <input type="hidden" name="advance" id="ff_pack_advance" value="0">
                <button type="submit" class="btn btn-default" onclick="$('#ff_pack_advance').val(0)">{{ __('fulfillment.save_packing') }}</button>
                <button type="submit" class="btn btn-primary btn-lg" onclick="$('#ff_pack_advance').val(1)">{{ __('fulfillment.mark_packed_ship') }}</button>
                {!! Form::close() !!}
            @else
                {!! Form::open(['url' => route('fulfillment.ship', $order->id)]) !!}
                <section class="ff-card">
                    <div class="ff-card__head">
                        <h3>{{ __('fulfillment.shipment') }}</h3>
                        @if ($stage === 'shipped')
                            <span class="ff-pill is-ok">{{ __('fulfillment.already_shipped') }}</span>
                        @else
                            <span class="ff-pill is-ok">{{ __('fulfillment.packed_ready_ship') }}</span>
                        @endif
                    </div>
                    <p>{{ $stage === 'shipped' ? __('fulfillment.already_shipped') : __('fulfillment.your_order_packed') }}</p>
                </section>
                <div class="ff-grid-2">
                    <section class="ff-card">
                        <h3>{{ __('fulfillment.package_details') }}</h3>
                        <p class="text-muted" style="font-size:12px;">{{ __('fulfillment.package_read_only') }}</p>
                        <p><strong>{{ __('fulfillment.package_type') }}:</strong> {{ $package['package_type'] ?: '—' }}</p>
                        <p><strong>{{ __('fulfillment.dimensions_in') }}:</strong> {{ ($package['length'] ?: '—') }} × {{ ($package['width'] ?: '—') }} × {{ ($package['height'] ?: '—') }}</p>
                        <p><strong>{{ __('fulfillment.weight_lb') }}:</strong> {{ $package['weight'] ?: '—' }}</p>
                        @if ($stage !== 'shipped')
                            <label class="ff-check"><input type="checkbox" name="package_sealed" value="1" @checked(!empty($ship['package_sealed']) || !empty($checklist['package_sealed']))> {{ __('fulfillment.package_sealed') }}</label>
                            <label class="ff-check"><input type="checkbox" name="label_attached" value="1" @checked(!empty($ship['label_attached']))> {{ __('fulfillment.label_attached') }}</label>
                            <label class="ff-check"><input type="checkbox" name="address_verified" value="1" @checked(!empty($ship['address_verified']))> {{ __('fulfillment.address_verified') }}</label>
                        @else
                            <p>{{ __('fulfillment.package_sealed') }}: {{ !empty($ship['package_sealed']) || !empty($checklist['package_sealed']) ? '✓' : '—' }}</p>
                            <p>{{ __('fulfillment.label_attached') }}: {{ !empty($ship['label_attached']) ? '✓' : '—' }}</p>
                            <p>{{ __('fulfillment.address_verified') }}: {{ !empty($ship['address_verified']) ? '✓' : '—' }}</p>
                        @endif
                    </section>
                    <section class="ff-card">
                        <h3>{{ __('fulfillment.carrier') }}</h3>
                        <label>{{ __('fulfillment.carrier') }}</label>
                        <input class="form-control" name="carrier" value="{{ $ship['carrier'] ?? $order->shipping_method }}" @disabled($stage === 'shipped')>
                        <label>{{ __('fulfillment.service_level') }}</label>
                        <input class="form-control" name="service" value="{{ $ship['service'] ?? '' }}" @disabled($stage === 'shipped')>
                        <label>{{ __('fulfillment.tracking_number') }}</label>
                        <input class="form-control" name="tracking" value="{{ $ship['tracking'] ?? $order->shipping_tracking_no }}" @disabled($stage === 'shipped')>
                    </section>
                </div>
                <section class="ff-card">
                    <h3>{{ __('fulfillment.items_in_order', ['count' => $order->sell_lines->count()]) }}</h3>
                    <table class="ff-items">
                        <thead>
                            <tr>
                                <th>@lang('sale.product')</th>
                                <th>SKU</th>
                                <th>{{ __('fulfillment.qty_ordered') }}</th>
                                <th>{{ __('fulfillment.qty_packed') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->sell_lines as $line)
                                <tr>
                                    <td>{{ $line->product->name ?? '' }}</td>
                                    <td>{{ $line->variations->sub_sku ?? '' }}</td>
                                    <td>{{ (float) $line->quantity }}</td>
                                    <td>{{ (float) ($line->qty_packed ?? $line->qty_picked ?? 0) }} <span class="ff-pill is-ok">{{ __('fulfillment.packed') }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </section>
                @if ($stage !== 'shipped')
                    <input type="hidden" name="confirm" value="1">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">{{ __('fulfillment.confirm_shipment') }}</button>
                @endif
                {!! Form::close() !!}
            @endif
        </div>

        <aside class="ff-side">
            @if ($stage === 'picking')
                {!! Form::open(['url' => route('fulfillment.start-picking', $order->id)]) !!}
                    <button type="submit" class="btn btn-primary btn-block btn-lg"><i class="fas fa-play"></i> {{ __('fulfillment.start_picking') }}</button>
                {!! Form::close() !!}
            @endif
            <section class="ff-card">
                <p><strong>{{ __('fulfillment.status') }}</strong><br>
                    @if ($stage === 'picking') {{ __('fulfillment.awaiting_picking') }}
                    @elseif ($stage === 'packing') {{ __('fulfillment.ready_to_pack') }}
                    @elseif ($stage === 'shipment') {{ __('fulfillment.packed_ready_ship') }}
                    @else {{ __('lang_v1.shipped') }}
                    @endif
                </p>
                <p><strong>{{ __('fulfillment.source') }}</strong><br>
                    <span class="ff-source-badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                </p>
            </section>
            <section class="ff-card">
                <h3>{{ __('fulfillment.order_summary') }}</h3>
                <dl class="ff-dl">
                    <dt>@lang('sale.invoice_no')</dt><dd>{{ $order->invoice_no }}</dd>
                    <dt>@lang('messages.date')</dt><dd>{{ \Carbon::parse($order->transaction_date)->format('M j, Y g:i A') }}</dd>
                    <dt>{{ __('fulfillment.source') }}</dt><dd>{{ $badge['label'] }}</dd>
                    <dt>@lang('sale.sub_total')</dt><dd><span class="display_currency" data-currency_symbol="true">{{ $order->total_before_tax }}</span></dd>
                    <dt>@lang('sale.shipping')</dt><dd><span class="display_currency" data-currency_symbol="true">{{ $order->shipping_charges }}</span></dd>
                    <dt>@lang('sale.tax')</dt><dd><span class="display_currency" data-currency_symbol="true">{{ $order->tax_amount }}</span></dd>
                    <dt>@lang('sale.total')</dt><dd class="ff-strong"><span class="display_currency" data-currency_symbol="true">{{ $order->final_total }}</span></dd>
                </dl>
            </section>
            @if (in_array($stage, ['packing', 'shipment', 'shipped'], true))
                <section class="ff-card">
                    <h3>{{ __('fulfillment.customer') }}</h3>
                    <p class="ff-strong">{{ $order->contact->name ?? '' }}</p>
                    <p>{{ $order->contact->email ?? '' }}</p>
                    <p>{{ $order->shipping_address ?: $order->shipping_details }}</p>
                </section>
            @endif
            <section class="ff-card">
                <h3>{{ __('fulfillment.activity') }}</h3>
                <ul class="ff-timeline">
                    @forelse ($timeline as $ev)
                        <li>
                            <strong>{{ $ev['label'] ?? '' }}</strong>
                            <span>{{ !empty($ev['at']) ? \Carbon::parse($ev['at'])->format('M j, Y g:i A') : '' }} · {{ $ev['by'] ?? '' }}</span>
                        </li>
                    @empty
                        <li>{{ __('lang_v1.no_data') }}</li>
                    @endforelse
                </ul>
            </section>
        </aside>
    </div>
</div>
</div>
@endsection

@section('javascript')
<script src="{{ asset('js/japs-fulfillment.js?v=' . $asset_v) }}"></script>
<script>if (typeof __currency_convert_recursively === 'function') { __currency_convert_recursively($('.ff-page')); }</script>
@endsection
