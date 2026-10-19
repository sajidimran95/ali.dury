@if ($paginator->hasPages())
    <nav class="gs-shop-pager" role="navigation">
        @if ($paginator->onFirstPage())
            <span>‹</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}">‹</a>
        @endif
        @foreach ($elements as $element)
            @if (is_string($element))
                <span>{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="is-current">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}">›</a>
        @else
            <span>›</span>
        @endif
    </nav>
@endif
