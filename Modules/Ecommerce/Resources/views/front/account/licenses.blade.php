@extends('ecommerce::front.layouts.store')
@section('title', 'Licenses')
@section('content')
@component('ecommerce::front.account.partials.shell', ['current' => 'ecommerce.account.licenses'])
    <div class="account-info-wrapper">
        <h4>Licenses</h4>
        <h5 style="margin-top:8px;font-weight:400;color:#666">{{ \Modules\Ecommerce\Support\WholesaleAccount::label($approval) }}</h5>
    </div>
    @forelse($licenses as $lic)
        <div class="order-row">
            <div>
                <strong>{{ $lic->label ?: $lic->license_type }}</strong>
                <div style="color:#808080;font-size:12px">
                    {{ $lic->certificate_number ?: '—' }}
                    @if($lic->expires_at) · Exp {{ $lic->expires_at->format('M j, Y') }}@endif
                </div>
            </div>
            @if($lic->file_path)
                <a href="{{ asset('uploads/'.$lic->file_path) }}" target="_blank" style="color:#dd772d">{{ $lic->original_name ?: 'View' }}</a>
            @endif
        </div>
    @empty
        <p style="font-size:14px;color:#808080;margin-bottom:16px">No licenses uploaded yet.</p>
    @endforelse
    <form method="post" action="{{ route('ecommerce.account.licenses', absolute: false) }}" enctype="multipart/form-data" style="margin-top:24px">
        @csrf
        <div class="billing-info">
            <label>Type</label>
            <select name="license_type" required>
                <option value="business">Business Certificate</option>
                <option value="resale">Resale Certificate</option>
                <option value="tobacco">Tobacco Certificate</option>
                <option value="id">ID Certificate</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="billing-info"><label>Label</label><input name="label"></div>
        <div class="billing-info"><label>Certificate number</label><input name="certificate_number"></div>
        <div class="billing-info"><label>Expiry</label><input type="date" name="expires_at"></div>
        <div class="billing-info"><label>File (PDF/JPG/PNG)</label><input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.webp"></div>
        <div class="billing-back-btn"><button class="billing-btn" type="submit">Submit license</button></div>
    </form>
@endcomponent
@endsection
