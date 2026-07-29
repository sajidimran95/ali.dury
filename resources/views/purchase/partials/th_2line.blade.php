{{-- Exactly 2 lines for purchase entry table headers (all screen sizes) --}}
@php
    $text = trim($text ?? '');
    $line1 = $text;
    $line2 = '';

    if ($text !== '') {
        if (preg_match('/^(.+?)\s+(\([^)]+\))$/u', $text, $m)) {
            $line1 = $m[1];
            $line2 = $m[2];
        } else {
            $words = preg_split('/\s+/u', $text);
            if (count($words) === 1) {
                $line1 = $words[0];
            } elseif (count($words) === 2) {
                $line1 = $words[0];
                $line2 = $words[1];
            } else {
                // Balance words across 2 lines (never more than 2)
                $mid = (int) ceil(count($words) / 2);
                $line1 = implode(' ', array_slice($words, 0, $mid));
                $line2 = implode(' ', array_slice($words, $mid));
            }
        }
    }
@endphp
@if($text !== '')
<span class="th-2line">
    <span class="th-line">{{ $line1 }}</span>
    @if($line2 !== '')
        <span class="th-line">{{ $line2 }}</span>
    @endif
</span>
@endif
