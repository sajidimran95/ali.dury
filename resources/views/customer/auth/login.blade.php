@extends('layouts.auth-shell')
@section('title', 'Customer Login')

@section('auth-brand')
<aside class="japs-auth-brand">
    <div class="japs-auth-brand-inner">
        <h2>{{ config('app.name', 'JAPS POS') }}</h2>
        <p>Customer portal — check prices, place orders, and view your invoices anytime.</p>
        <ul class="japs-auth-brand-features">
            <li>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Create orders
            </li>
            <li>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                View invoices
            </li>
            <li>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Price check
            </li>
        </ul>
    </div>
</aside>
@endsection

@section('auth-title')
    Welcome back
@endsection

@section('auth-subtitle')
    Sign in to the Customer App — {{ config('app.name', 'JAPS POS') }}
@endsection

@section('auth-content')
<style>
    body.customer-auth {
        --ca-brand: #e11d48;
        --theme-700: #e11d48;
        --theme-800: #be123c;
        background:
            radial-gradient(1200px 600px at 10% -10%, rgba(225, 29, 72, .12), transparent 55%),
            radial-gradient(900px 500px at 100% 0%, rgba(251, 113, 133, .14), transparent 50%),
            linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%) !important;
    }
    body.customer-auth .japs-auth-brand {
        background: linear-gradient(145deg, #881337 0%, #be123c 42%, #e11d48 100%) !important;
    }
    body.customer-auth .japs-auth-brand::before {
        background:
            radial-gradient(ellipse 80% 60% at 15% 85%, rgba(255, 228, 230, .28), transparent),
            radial-gradient(ellipse 60% 50% at 90% 8%, rgba(251, 113, 133, .35), transparent) !important;
    }
    body.customer-auth .japs-auth-card {
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .06), 0 20px 50px rgba(225, 29, 72, .12);
        border: 1px solid rgba(225, 29, 72, .08);
    }
    body.customer-auth .japs-auth-input:focus,
    body.customer-auth .japs-auth-card input[type="text"]:focus,
    body.customer-auth .japs-auth-card input[type="password"]:focus {
        border-color: #e11d48 !important;
        box-shadow: 0 0 0 3px rgba(225, 29, 72, .15) !important;
    }
    body.customer-auth .japs-auth-btn {
        background: linear-gradient(135deg, #e11d48 0%, #be123c 100%) !important;
        box-shadow: 0 10px 24px rgba(225, 29, 72, .28) !important;
        border-radius: 14px;
        font-weight: 800;
    }
    body.customer-auth .japs-auth-btn:hover {
        filter: brightness(1.05);
        box-shadow: 0 14px 28px rgba(225, 29, 72, .34) !important;
    }
    body.customer-auth input[type="checkbox"] { accent-color: #e11d48; }
</style>
<script>document.body.classList.add('customer-auth');</script>

    @if(session('error'))
        <div class="alert alert-danger" style="border-radius:11px;margin-bottom:16px;">
            <strong>{{ session('error') }}</strong>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" style="border-radius:11px;margin-bottom:16px;">
            <strong>{{ $errors->first() }}</strong>
        </div>
    @endif

    <form method="POST" action="{{ route('customer.login.post') }}" id="customer-login-form">
        @csrf
        <div class="form-group has-feedback {{ $errors->has('login') ? ' has-error' : '' }}">
            <label class="tw-dw-form-control">
                <div class="tw-dw-label">
                    <span class="tw-text-xs md:tw-text-sm tw-font-medium tw-text-slate-700">Email or Mobile</span>
                </div>
                <input class="japs-auth-input" name="login" required autofocus
                       placeholder="email@example.com or mobile number" id="login" type="text"
                       value="{{ old('login') }}" autocomplete="username"
                       inputmode="email" />
            </label>
        </div>

        <div class="form-group has-feedback {{ $errors->has('password') ? ' has-error' : '' }}">
            <label class="tw-dw-form-control" style="position:relative;">
                <div class="tw-dw-label">
                    <span class="tw-text-xs md:tw-text-sm tw-font-medium tw-text-slate-700">Password</span>
                </div>
                <input class="japs-auth-input japs-auth-input--password" id="password" type="password"
                       name="password" required placeholder="Password" autocomplete="current-password" />
                <button type="button" id="show_hide_icon" class="japs-auth-toggle-pw" style="position:absolute;right:10px;bottom:14px;" aria-label="Show password">
                    <svg xmlns="http://www.w3.org/2000/svg" class="tw-w-5 tw-h-5 tw-text-slate-500" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
                </button>
            </label>
        </div>

        <div class="tw-dw-form-control tw-mb-4">
            <label class="tw-dw-cursor-pointer tw-dw-label tw-self-start tw-gap-2">
                <input type="checkbox" name="remember" value="1" class="tw-dw-checkbox" {{ old('remember') ? 'checked' : '' }}>
                <span class="tw-text-sm tw-font-medium tw-text-slate-600">Remember me</span>
            </label>
        </div>

        <button type="submit" class="japs-auth-btn tw-mt-2">Login</button>
    </form>
@endsection

@section('auth-footer')
    <p class="tw-text-center tw-text-sm tw-text-slate-500 tw-mb-0">
        Customer portal for {{ config('app.name', 'JAPS POS') }}
    </p>
@endsection

@section('javascript')
@include('customer.partials.pwa')
<script type="text/javascript">
    $(document).ready(function () {
        $('#show_hide_icon').on('click', function (e) {
            e.preventDefault();
            const passwordInput = $('#password');
            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                $(this).html('<svg xmlns="http://www.w3.org/2000/svg" class="tw-w-5 tw-h-5 tw-text-slate-500" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z"/><path d="M10.585 10.587a2 2 0 0 0 2.829 2.828"/><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.666 1.11 -1.379 2.067 -2.138 2.87"/><path d="M3 3l18 18"/></svg>');
            } else {
                passwordInput.attr('type', 'password');
                $(this).html('<svg xmlns="http://www.w3.org/2000/svg" class="tw-w-5 tw-h-5 tw-text-slate-500" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>');
            }
        });
    });
</script>
@endsection
