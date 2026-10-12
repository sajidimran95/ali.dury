@extends('ecommerce::front.layouts.store')
@section('title', __('ecommerce::lang.track_order'))
@section('content')
@php
    $statusLabel = ucfirst($meta->fulfillment_status ?? 'pending');
    if (!empty($converted_invoices)) {
        $statusLabel = __('ecommerce::lang.order_invoiced');
    }
@endphp
<div class="max-w-xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-extrabold mb-6">@lang('ecommerce::lang.track_order')</h1>
    <form method="get" class="flex gap-2 mb-8">
        <input name="token" value="{{ $token }}" placeholder="Tracking token" class="flex-1 wn-field">
        <button class="btn-brand">Track</button>
    </form>
    @if($meta && $order)
        <div class="wn-panel p-6 space-y-5">
            <div>
                <div class="font-semibold text-lg">{{ $order->invoice_no }}</div>
                <div class="text-sm text-[#8b95b8] mt-1">
                    @lang('ecommerce::lang.fulfillment'):
                    <span class="inline-flex items-center rounded-full bg-[#1a2140] px-2.5 py-0.5 text-xs font-bold">{{ $statusLabel }}</span>
                </div>
                @if($order->type === 'sales_order')
                    <div class="text-sm text-[#8b95b8] mt-1">
                        @lang('ecommerce::lang.sales_order_status'):
                        <span class="font-medium">{{ ucfirst($order->status) }}</span>
                    </div>
                @endif
            </div>

            @if(!empty($converted_invoices))
                <div class="rounded-xl border border-[#3ee07a55] bg-[#10281c] px-4 py-3">
                    <div class="text-xs font-bold uppercase tracking-wide text-[#3ee07a] mb-2">@lang('ecommerce::lang.invoice_created')</div>
                    <ul class="space-y-1 text-sm">
                        @foreach($converted_invoices as $invoice)
                            <li class="font-semibold">
                                #{{ $invoice->invoice_no }}
                                <span class="text-[#8b95b8] font-normal">· {{ @format_datetime($invoice->transaction_date ?? $invoice->created_at) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <ol class="space-y-3 text-sm">
                @forelse(($timeline ?? $meta->timeline ?? []) as $step)
                    <li class="flex justify-between gap-3 border-b border-[#252d4d] pb-2">
                        <span>{{ $step['label'] ?? ucfirst($step['status'] ?? '') }}
                            @if(!empty($step['invoice_no']) && empty($step['label']))
                                — #{{ $step['invoice_no'] }}
                            @endif
                        </span>
                        <span class="text-[#8b95b8] shrink-0">{{ $step['at'] ?? '' }}</span>
                    </li>
                @empty
                    <li class="text-[#8b95b8]">No updates yet.</li>
                @endforelse
            </ol>
        </div>
    @elseif($token)
        <p class="text-rose-400 text-sm">Order not found.</p>
    @endif
</div>
@endsection
