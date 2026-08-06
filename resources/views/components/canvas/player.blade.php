@props(['element'])

@php
    $isHome = $element['team'] === App\Enums\PlayerTeam::Home->value;
@endphp

<circle cx="{{ $element['x'] }}" cy="{{ $element['y'] }}" r="17"
        fill="{{ $isHome ? '#1d4ed8' : '#dc2626' }}"
        stroke="#ffffff" stroke-width="2.5" />

<text x="{{ $element['x'] }}" y="{{ $element['y'] }}"
      text-anchor="middle" dominant-baseline="central"
      font-size="18" font-weight="700" fill="#ffffff">{{ $element['number'] }}</text>
