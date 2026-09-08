@extends('sale.layout')

@section('title', 'Stock Transfer Details')

@section('content')
<div class="sale-page">
    <div class="sale-page__head">
        <div class="flex items-center gap-3">
            <a href="{{ route('sale.transfers') }}" class="flex items-center justify-center w-9 h-9 rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-xl font-extrabold text-sale-ink m-0 leading-tight">
                Transfer Details
            </h1>
        </div>
    </div>

    @php
        $child = $transfer->transfer_child;
        $locationFrom = $transfer->location->name ?? 'Unknown';
        $locationTo = $child ? ($child->location->name ?? 'Unknown') : 'Unknown';
        $status = $child ? ($child->status == 'final' ? 'completed' : $child->status) : $transfer->status;
        $statusColors = [
            'pending' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'label' => 'Pending'],
            'in_transit' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'label' => 'In Transit'],
            'completed' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'label' => 'Completed'],
        ];
        $statusData = $statusColors[$status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'label' => ucfirst($status)];
    @endphp

    <div class="mt-4 space-y-3">
        <!-- Transfer Info Card -->
        <div class="sale-card">
            <div class="flex items-start justify-between gap-3 mb-4">
                <div>
                    <div class="text-xs text-slate-500 mb-1">Reference</div>
                    <div class="font-bold text-lg">{{ $transfer->ref_no }}</div>
                </div>
                <div class="text-xs font-semibold px-3 py-1.5 rounded-full {{ $statusData['bg'] }} {{ $statusData['text'] }}">
                    {{ $statusData['label'] }}
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="text-xs text-slate-500 mb-1">Date</div>
                    <div class="font-semibold text-sm">{{ \Carbon\Carbon::parse($transfer->transaction_date)->format('M d, Y g:i A') }}</div>
                </div>
                @if($transfer->final_total)
                <div>
                    <div class="text-xs text-slate-500 mb-1">Total Amount</div>
                    <div class="font-bold text-sm text-sale">{{ number_format($transfer->final_total, 2) }}</div>
                </div>
                @endif
            </div>

            <div class="mt-4 pt-4 border-t border-slate-200">
                <div class="flex items-center gap-3">
                    <div class="flex-1">
                        <div class="text-xs text-slate-500 mb-1">From Location</div>
                        <div class="px-3 py-2 bg-blue-50 text-blue-700 rounded-lg font-semibold text-sm">
                            {{ $locationFrom }}
                        </div>
                    </div>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-slate-400 shrink-0">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                    <div class="flex-1">
                        <div class="text-xs text-slate-500 mb-1">To Location</div>
                        <div class="px-3 py-2 bg-purple-50 text-purple-700 rounded-lg font-semibold text-sm">
                            {{ $locationTo }}
                        </div>
                    </div>
                </div>
            </div>

            @if($transfer->shipping_charges && $transfer->shipping_charges > 0)
            <div class="mt-4 pt-4 border-t border-slate-200">
                <div class="flex justify-between items-center">
                    <span class="text-xs text-slate-500">Shipping Charges</span>
                    <span class="font-semibold text-sm">{{ number_format($transfer->shipping_charges, 2) }}</span>
                </div>
            </div>
            @endif

            @if($transfer->additional_notes)
            <div class="mt-4 pt-4 border-t border-slate-200">
                <div class="text-xs text-slate-500 mb-1">Notes</div>
                <div class="text-sm text-slate-700 p-3 bg-slate-50 rounded-lg">
                    {{ $transfer->additional_notes }}
                </div>
            </div>
            @endif
        </div>

        <!-- Products List -->
        @if($child && $child->sell_lines && $child->sell_lines->count() > 0)
        <div class="sale-card">
            <div class="sale-sec-title">
                <div class="sale-sec-title__ico">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/>
                        <rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                </div>
                Products ({{ $child->sell_lines->count() }})
            </div>

            <div class="space-y-2">
                @foreach($child->sell_lines as $line)
                    <div class="flex items-start justify-between gap-3 p-3 bg-slate-50 rounded-lg">
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-sm text-sale-ink">
                                {{ $line->product->name ?? 'Unknown Product' }}
                            </div>
                            @if($line->product && $line->product->sku)
                                <div class="text-xs text-slate-500 mt-0.5">
                                    SKU: {{ $line->product->sku }}
                                </div>
                            @endif
                            @if($line->variations && $line->variations->name)
                                <div class="text-xs text-slate-600 mt-1">
                                    {{ $line->variations->name }}
                                </div>
                            @endif
                        </div>
                        <div class="text-right shrink-0">
                            <div class="font-bold text-sm text-sale-ink">
                                Qty: {{ number_format($line->quantity, 2) }}
                            </div>
                            @if($line->unit_price_inc_tax)
                                <div class="text-xs text-slate-500 mt-0.5">
                                    @ {{ number_format($line->unit_price_inc_tax, 2) }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
