{{-- Purchase table header: full words, wrap only at spaces (no letter-break) --}}
@php
    $text = trim($text ?? '');
@endphp
@if($text !== '')
<span class="th-2line">{{ $text }}</span>
@endif
