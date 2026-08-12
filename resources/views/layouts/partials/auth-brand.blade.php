<aside class="japs-auth-brand">
    <div class="japs-auth-brand-inner">
        <h2>{{ $brand_title ?? config('app.name', 'Japs POS') }}</h2>
        <p>{{ $brand_subtitle ?? __('lang_v1.login_to_your') . ' ' . config('app.name', 'Japs POS') }}</p>
        @if (!empty($brand_items))
            <ul class="japs-auth-brand-features">
                @foreach ($brand_items as $item)
                    <li>
                        @if (!empty($item['icon']))
                            {!! $item['icon'] !!}
                        @endif
                        {{ $item['text'] }}
                    </li>
                @endforeach
            </ul>
        @else
            <ul class="japs-auth-brand-features">
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @lang('home.total_sell')
                </li>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    @lang('home.total_purchase')
                </li>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    @lang('lang_v1.welcome_back')
                </li>
            </ul>
        @endif
    </div>
</aside>
