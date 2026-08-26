@extends('sale.layout')
@section('title', 'Expense')
@section('header', 'Expense')
@section('content')
{{-- Same app shell as other bottom tabs --}}
<div class="sale-page-tool">
    <span class="sale-chip">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"/><path d="M2 10h20M6 14h.01M10 14h4"/></svg>
        {{ $expenses->total() }} expense(s)
    </span>
    @if(!empty($canAdd))
        <a href="{{ route('sale.expenses.create') }}" class="sale-btn-sm">ADD NEW EXPENSE</a>
    @endif
</div>

<div class="space-y-2">
    @forelse($expenses as $expense)
        <div class="sale-order-row !cursor-default">
            <span class="sale-order-row__ico">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="6" width="20" height="12" rx="2"/><path d="M2 10h20M6 14h.01M10 14h4"/></svg>
            </span>
            <div class="sale-order-row__body">
                <div class="font-extrabold text-[15px] truncate">
                    {{ optional($expense->expense_category)->name ?: ($expense->ref_no ?: 'Expense') }}
                </div>
                <div class="text-xs text-slate-500 mt-0.5 truncate">
                    {{ \Carbon\Carbon::parse($expense->transaction_date)->format('M j, Y') }}
                    @if(!empty(optional($expense->location)->name))
                        · {{ $expense->location->name }}
                    @endif
                    @if(!empty(optional($expense->contact)->supplier_business_name) || !empty(optional($expense->contact)->name))
                        · {{ $expense->contact->supplier_business_name ?: $expense->contact->name }}
                    @endif
                </div>
                @if(!empty($expense->additional_notes))
                    <div class="text-sm text-slate-600 mt-1 truncate">{{ $expense->additional_notes }}</div>
                @endif
            </div>
            <div class="sale-order-row__meta">
                <div class="font-extrabold tabular-nums">${{ number_format((float) $expense->final_total, 2) }}</div>
            </div>
        </div>
    @empty
        <div class="sale-card sale-empty">
            <div class="sale-empty__ico">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/><path d="M14 3v5h5M9 13h6M9 17h4"/></svg>
            </div>
            <p class="text-slate-500 text-sm mb-1">No Expenses Found</p>
            <p class="text-slate-400 text-xs mb-4">Start by adding your first expense</p>
            @if(!empty($canAdd))
                <a href="{{ route('sale.expenses.create') }}" class="sale-btn inline-block w-auto px-6">Add New Expense</a>
            @endif
        </div>
    @endforelse
</div>

@if($expenses->hasPages())
    <div class="mt-3">
        @include('sale.partials.pagination', ['paginator' => $expenses])
    </div>
@endif
@endsection
