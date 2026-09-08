@extends('sale.layout')

@section('title', 'Stock Transfers')

@section('content')
<div class="sale-page">
    <!-- Top Bar with Search and + Button -->
    <div class="px-4 py-3 bg-white border-b border-slate-200 sticky top-0 z-10">
        <div class="flex items-center gap-3 mb-3">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>
                <input 
                    type="text" 
                    id="transferSearch" 
                    placeholder="Search" 
                    class="w-full pl-10 pr-4 py-2 bg-slate-50 border-0 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-sale/20 focus:bg-white"
                >
            </div>
            <a href="{{ route('sale.transfers.create') }}" class="w-9 h-9 flex items-center justify-center rounded-full border-2 border-sale text-sale">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
            </a>
        </div>
        
        <div class="text-xs text-slate-500">
            Results: <span id="resultCount">{{ $transfers->total() }}</span>
        </div>
    </div>

    @if(session('status'))
        <div class="mx-4 mt-3 p-3 rounded-xl {{ session('status')['success'] ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' }}">
            <div class="flex items-center gap-2 text-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    @if(session('status')['success'])
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <path d="M22 4 12 14.01l-3-3"/>
                    @else
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    @endif
                </svg>
                <span class="font-medium">{{ session('status')['msg'] }}</span>
            </div>
        </div>
    @endif

    <div class="p-4 space-y-3">
        @forelse($transfers as $transfer)
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
            
            <a href="{{ route('sale.transfers.show', $transfer->id) }}" class="sale-card block hover:shadow-md transition-shadow transfer-item" data-ref="{{ $transfer->ref_no }}" data-locations="{{ $locationFrom }} {{ $locationTo }}" data-notes="{{ $transfer->additional_notes ?? '' }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="sale-sec-title__ico">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                    <path d="M3.3 7 12 12l8.7-5M12 22V12"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-sm text-sale-ink">
                                    Ref: {{ $transfer->ref_no }}
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    {{ \Carbon\Carbon::parse($transfer->transaction_date)->format('M d, Y g:i A') }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2 text-xs mt-2">
                            <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded font-semibold">{{ $locationFrom }}</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-slate-400">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                            <span class="px-2 py-1 bg-purple-50 text-purple-700 rounded font-semibold">{{ $locationTo }}</span>
                        </div>

                        @if($transfer->additional_notes)
                            <div class="text-xs text-slate-600 mt-2 p-2 bg-slate-50 rounded-lg line-clamp-2">
                                {{ $transfer->additional_notes }}
                            </div>
                        @endif
                    </div>
                    
                    <div class="text-right shrink-0">
                        <div class="text-xs font-semibold px-3 py-1.5 rounded-full {{ $statusData['bg'] }} {{ $statusData['text'] }} mb-2">
                            {{ $statusData['label'] }}
                        </div>
                        @if($transfer->final_total)
                            <div class="text-sm font-bold text-slate-700">
                                {{ number_format($transfer->final_total, 2) }}
                            </div>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="sale-empty">
                <div class="sale-empty__ico">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <path d="M3.3 7 12 12l8.7-5M12 22V12"/>
                    </svg>
                </div>
                <h3 class="font-bold text-base mb-1">No Result</h3>
                <p class="text-sm text-slate-600 mb-4">
                    No stock transfers found
                </p>
            </div>
        @endforelse
    </div>

    @if($transfers->hasPages())
        <div class="sale-pager mt-4 mx-4">
            <div class="sale-pager__meta">
                Showing {{ $transfers->firstItem() }} to {{ $transfers->lastItem() }} of {{ $transfers->total() }}
            </div>
            <div class="sale-pager__btns">
                @if($transfers->onFirstPage())
                    <span class="sale-pager__btn sale-pager__btn--disabled">Prev</span>
                @else
                    <a href="{{ $transfers->previousPageUrl() }}" class="sale-pager__btn">Prev</a>
                @endif

                @if($transfers->hasMorePages())
                    <a href="{{ $transfers->nextPageUrl() }}" class="sale-pager__btn">Next</a>
                @else
                    <span class="sale-pager__btn sale-pager__btn--disabled">Next</span>
                @endif
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('transferSearch');
    const transferItems = document.querySelectorAll('.transfer-item');
    const resultCount = document.getElementById('resultCount');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            let visibleCount = 0;
            
            transferItems.forEach(item => {
                const ref = (item.dataset.ref || '').toLowerCase();
                const locations = (item.dataset.locations || '').toLowerCase();
                const notes = (item.dataset.notes || '').toLowerCase();
                
                if (ref.includes(searchTerm) || locations.includes(searchTerm) || notes.includes(searchTerm)) {
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            if (resultCount) {
                resultCount.textContent = visibleCount;
            }
        });
    }
});
</script>
@endsection
