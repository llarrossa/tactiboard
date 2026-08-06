@props(['element', 'selected' => false])

{{-- O contorno escuro (paint-order: stroke) mantem a observacao legivel sobre
     qualquer parte do gramado, inclusive por cima das linhas brancas. --}}
<text x="{{ $element['x'] }}" y="{{ $element['y'] }}"
      text-anchor="middle" dominant-baseline="central"
      font-size="24" font-weight="600"
      fill="#ffffff" stroke="#14532d" stroke-width="5" paint-order="stroke">{{ $element['content'] }}</text>
