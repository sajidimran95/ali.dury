@extends('sale.layout')
@section('title', 'History')
@section('header', 'History')
@section('header_left')
<a href="{{ route('sale.customers.show', $customer->id) }}" class="h-9 w-9 flex items-center justify-center text-inherit no-underline" aria-label="Back">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg>
</a>
@endsection
@section('header_right')
<button type="button" class="h-9 w-9 border-0 bg-transparent" id="saleHistSearchBtn" aria-label="Search" onclick="document.getElementById('saleHistSearch').classList.toggle('hidden')">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
</button>
@endsection
@section('content')
<form method="GET" action="{{ route('sale.customers.history', $customer->id) }}" id="saleHistSearch" class="{{ ($q ?? '') !== '' ? '' : 'hidden' }} mb-2">
    <input type="search" name="q" value="{{ $q ?? '' }}" class="sale-input" placeholder="Search" autofocus>
</form>
@include('sale.customers.partials.head', ['layout' => 'split'])
<div class="text-center text-xs text-slate-400 mb-2">Results: {{ $history->count() }}</div>
@forelse($history as $row)
    @include('sale.customers.partials.history_card', ['row' => $row])
@empty
    <div class="sale-card sale-empty text-slate-500 text-sm">No orders or invoices.</div>
@endforelse
@endsection
