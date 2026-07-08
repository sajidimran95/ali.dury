@php
    $group_icons = [
        'purchase' =>
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
        'sale' =>
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v18"/><path d="M16 7l-4-4-4 4"/><path d="M3 12a9 9 0 0 0 18 0"/></svg>',
        'inventory' =>
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 7.5v9L12 21l8-4.5v-9z"/><path d="M12 12 4 7.5"/><path d="M12 12v9"/><path d="M12 12l8-4.5"/></svg>',
    ];
@endphp

@forelse ($groups as $group)
    <div class="zoho-side-panel__group">
        <div class="zoho-side-panel__group-head">
            <span class="zoho-side-panel__group-icon zoho-side-panel__group-icon--{{ $group['icon'] }}">
                {!! $group_icons[$group['icon']] ?? '' !!}
            </span>
            <span class="zoho-side-panel__group-title">{{ $group['title'] }}</span>
        </div>

        <div class="zoho-side-panel__list">
            @foreach ($group['items'] as $item)
                <a href="{{ $item['url'] }}" class="zoho-side-panel__row">
                    <svg class="zoho-side-panel__row-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6l-6 6"/></svg>
                    <span class="zoho-side-panel__row-label">{{ $item['label'] }}</span>
                    <span class="zoho-side-panel__row-count {{ $item['count'] > 0 ? 'zoho-side-panel__row-count--active' : '' }}">{{ $item['count'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
@empty
    <div class="zoho-side-panel__empty">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5l10 -10"/></svg>
        <p>@lang('lang_v1.no_pending_actions')</p>
    </div>
@endforelse
