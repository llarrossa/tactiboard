@props(['element'])

@php
    $x = $element['x'];
    $y = $element['y'];
@endphp

{{-- O cone e desenhado centrado em (x, y) para que arrastar o elemento leve o
     ponto de referencia junto com a figura. --}}
<polygon points="{{ $x }},{{ $y - 14 }} {{ $x - 11 }},{{ $y + 10 }} {{ $x + 11 }},{{ $y + 10 }}"
         fill="#f97316" stroke="#7c2d12" stroke-width="1.5" />
