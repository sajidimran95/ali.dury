@extends('sale.layout')
@section('title', 'Saved orders')
@section('header', 'Saved orders')
@section('content')
<div class="sale-rpt-list">
    @forelse($saved_orders as $row)
        @php
            $continueUrl = route('sale.orders.create', [
                'contact_id' => $row['contact_id'] ?? '',
                'saved' => $row['id'] ?? '',
            ]);
            $when = !empty($row['saved_at']) ? \Carbon\Carbon::parse($row['saved_at'])->format('M j, Y g:i A') : '';
        @endphp
        <div class="sale-order-row !items-stretch">
            <a href="{{ $continueUrl }}" class="sale-order-row__ico shrink-0 self-center">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/></svg>
            </a>
            <a href="{{ $continueUrl }}" class="sale-order-row__body min-w-0 flex-1 no-underline text-inherit">
                <div class="font-extrabold text-[15px] truncate">{{ $row['contact_name'] ?? 'Customer' }}</div>
                <div class="text-xs text-slate-400 mt-1">{{ $when }} · {{ count($row['cart'] ?? []) }} item(s)</div>
            </a>
            <div class="sale-order-row__meta shrink-0">
                <div class="font-extrabold tabular-nums">${{ number_format((float) ($row['total'] ?? 0), 2) }}</div>
                <span class="sale-badge sale-badge--draft mt-1">Saved</span>
                <form method="POST" action="{{ route('sale.saved_orders.destroy', $row['id']) }}" class="mt-2" onsubmit="return confirm('Remove this saved order?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs font-bold text-rose-500">Remove</button>
                </form>
            </div>
        </div>
    @empty
        <div class="sale-card sale-empty">
            <p class="text-slate-500 text-sm">No saved orders. On Check Out, tap X then YES to save and continue later.</p>
        </div>
    @endforelse
</div>
@endsection
