@extends('layouts.auth2')

@section('content')
    @include('layouts.partials.auth-topbar')

    <div class="japs-auth-page">
        @hasSection('auth-brand')
            @yield('auth-brand')
        @else
            @include('layouts.partials.auth-brand')
        @endif

        <main class="japs-auth-main @hasSection('auth-wide') japs-auth-main--wide @endif">
            @yield('auth-before-card')

            <div class="japs-auth-card @hasSection('auth-wide') japs-auth-card--wide @endif">
                @hasSection('auth-title')
                    <div class="japs-auth-card-logo">
                        <img src="{{ asset('img/logo-small.png') }}" alt="" />
                    </div>
                    <h1>@yield('auth-title')</h1>
                    @hasSection('auth-subtitle')
                        <p class="japs-auth-subtitle">@yield('auth-subtitle')</p>
                    @endif
                @endif

                @yield('auth-content')

                @hasSection('auth-footer')
                    <div class="japs-auth-footer">
                        @yield('auth-footer')
                    </div>
                @endif
            </div>

            @yield('auth-after-card')
        </main>
    </div>
@endsection
