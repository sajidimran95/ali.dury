@if ($paginator->hasPages())
    <nav class="flex items-center justify-center gap-2 text-sm" role="navigation">
        @if ($paginator->onFirstPage())
            <span class="px-3 py-1.5 rounded-full text-[#8b95b8] border border-[#252d4d]">Prev</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1.5 rounded-full border border-[#252d4d] hover:border-[#f23488]">Prev</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 text-[#8b95b8]">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="px-3 py-1.5 rounded-lg bg-[#f23488] text-white font-bold">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="px-3 py-1.5 rounded-full border border-[#252d4d] hover:border-[#f23488]">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1.5 rounded-full border border-[#252d4d] hover:border-[#f23488]">Next</a>
        @else
            <span class="px-3 py-1.5 rounded-full text-[#8b95b8] border border-[#252d4d]">Next</span>
        @endif
    </nav>
@endif
