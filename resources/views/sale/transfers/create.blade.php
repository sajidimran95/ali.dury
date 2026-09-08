@extends('sale.layout')

@section('title', 'New Stock Transfer')

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
                New Stock Transfer
            </h1>
        </div>
    </div>

    @if($errors->any())
        <div class="mt-4 p-4 rounded-xl bg-red-50 text-red-800 border border-red-200">
            <div class="font-semibold mb-2">Please fix the following errors:</div>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('sale.transfers.store') }}" id="transferForm" class="mt-4">
        @csrf

        <div class="space-y-4">
            <!-- Date -->
            <div class="sale-card">
                <label class="block text-sm font-bold text-sale-ink mb-2">
                    Date <span class="text-sale">*</span>
                </label>
                <input 
                    type="datetime-local" 
                    name="transaction_date" 
                    class="sale-input" 
                    value="{{ old('transaction_date', now()->format('Y-m-d\TH:i')) }}"
                    required
                >
            </div>

            <!-- Reference Number -->
            <div class="sale-card">
                <label class="block text-sm font-bold text-sale-ink mb-2">
                    Reference No
                </label>
                <input 
                    type="text" 
                    name="ref_no" 
                    class="sale-input" 
                    placeholder="Auto-generated if empty"
                    value="{{ old('ref_no') }}"
                >
            </div>

            <!-- Status -->
            <div class="sale-card">
                <label class="block text-sm font-bold text-sale-ink mb-2">
                    Status <span class="text-sale">*</span>
                </label>
                <select name="status" class="sale-input" required>
                    <option value="">Select status</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" {{ old('status', 'pending') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-500 mt-1">
                    💡 Use "Completed" if stock is transferred immediately
                </p>
            </div>

            <!-- Location From -->
            <div class="sale-card">
                <label class="block text-sm font-bold text-sale-ink mb-2">
                    Location From <span class="text-sale">*</span>
                </label>
                <select name="location_id" id="locationFrom" class="sale-input" required>
                    <option value="">Select source location</option>
                    @foreach($business_locations as $id => $name)
                        <option value="{{ $id }}" {{ old('location_id') == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Location To -->
            <div class="sale-card">
                <label class="block text-sm font-bold text-sale-ink mb-2">
                    Location To <span class="text-sale">*</span>
                </label>
                <select name="transfer_location_id" id="locationTo" class="sale-input" required>
                    <option value="">Select destination location</option>
                    @foreach($business_locations as $id => $name)
                        <option value="{{ $id }}" {{ old('transfer_location_id') == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Product Search -->
            <div class="sale-card">
                <label class="block text-sm font-bold text-sale-ink mb-3">
                    Products <span class="text-sale">*</span>
                </label>
                
                <div class="relative mb-3">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input 
                        type="text" 
                        id="productSearch" 
                        placeholder="Search products..."
                        class="w-full pl-10 pr-4 py-3 bg-slate-50 border-0 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-sale/20 focus:bg-white"
                        disabled
                    >
                </div>

                <div id="productList" class="space-y-2 mb-3">
                    <!-- Products will be added here -->
                </div>

                <input type="hidden" name="final_total" id="finalTotal" value="0">
                
                <div class="text-right pt-3 border-t border-slate-200">
                    <div class="text-sm text-slate-600 mb-1">Total Amount</div>
                    <div class="text-2xl font-bold text-sale">
                        <span id="totalDisplay">0.00</span>
                    </div>
                </div>
            </div>

            <!-- Shipping Charges -->
            <div class="sale-card">
                <label class="block text-sm font-bold text-sale-ink mb-2">
                    Shipping Charges
                </label>
                <input 
                    type="number" 
                    name="shipping_charges" 
                    class="sale-input" 
                    placeholder="0.00" 
                    step="0.01" 
                    min="0"
                    value="{{ old('shipping_charges', 0) }}"
                >
            </div>

            <!-- Additional Notes -->
            <div class="sale-card">
                <label class="block text-sm font-bold text-sale-ink mb-2">
                    Additional Notes
                </label>
                <textarea 
                    name="additional_notes" 
                    class="sale-input" 
                    rows="4" 
                    placeholder="Add any notes about this transfer..."
                >{{ old('additional_notes') }}</textarea>
            </div>

            <!-- Info Box -->
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
                <div class="flex items-start gap-3">
                    <div class="shrink-0 mt-0.5">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="16" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                    </div>
                    <div class="text-xs text-blue-800">
                        <p class="font-semibold mb-1">Stock Transfer Information</p>
                        <p>This will create a stock transfer transaction from the source location to the destination location. Products will be deducted from source and added to destination based on status.</p>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-3 pt-2 pb-6">
                <button type="submit" class="sale-btn flex-1">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="inline-block mr-1">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Save Transfer
                </button>
                <a href="{{ route('sale.transfers') }}" class="sale-btn-ghost flex-1 text-center">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const locationFrom = document.getElementById('locationFrom');
    const locationTo = document.getElementById('locationTo');
    const productSearch = document.getElementById('productSearch');
    
    // Validation: prevent selecting same locations
    function validateLocations() {
        if (locationFrom.value && locationTo.value && locationFrom.value === locationTo.value) {
            locationTo.setCustomValidity('Destination location must be different from source location');
        } else {
            locationTo.setCustomValidity('');
        }
    }
    
    if (locationFrom && locationTo) {
        locationFrom.addEventListener('change', function() {
            validateLocations();
            if (this.value) {
                productSearch.disabled = false;
            } else {
                productSearch.disabled = true;
            }
        });
        locationTo.addEventListener('change', validateLocations);
    }

    // Prevent form submission without products
    document.getElementById('transferForm').addEventListener('submit', function(e) {
        const productList = document.getElementById('productList');
        if (!productList.children.length) {
            e.preventDefault();
            alert('Please add at least one product to the transfer');
            return false;
        }
    });
});
</script>
@endsection
