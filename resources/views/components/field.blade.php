@props(['class' => ''])

@php
    // O gramado e o sistema de coordenadas do canvas, entao as dimensoes vem
    // de CanvasRules — a mesma classe que valida se um elemento caiu fora do
    // campo. A margem so abre espaco para as traves e a grama em volta.
    $width = App\Rules\CanvasRules::FIELD_WIDTH;
    $height = App\Rules\CanvasRules::FIELD_HEIGHT;
    $margin = 30;
@endphp

{{--
    Campo de futebol nas medidas oficiais da IFAB (105 m x 68 m), desenhado em
    SVG com um sistema de coordenadas proprio: 1 unidade = 1 decimetro, entao o
    gramado vai de (0,0) a (1050,680).

    Esse e o sistema em que as coordenadas dos elementos sao persistidas em
    canvas_data. Ele nao depende da resolucao da tela — o SVG escala por CSS e o
    JSON continua valendo em qualquer tamanho. Ver docs/03 secao 6.1.

    O viewBox e maior que o gramado para caber as traves e uma margem de grama;
    isso nao muda o sistema de coordenadas dos elementos, que permanece
    0..1050 no eixo x e 0..680 no eixo y.
--}}

<svg viewBox="{{ -$margin }} {{ -$margin }} {{ $width + $margin * 2 }} {{ $height + $margin * 2 }}"
     xmlns="http://www.w3.org/2000/svg"
     preserveAspectRatio="xMidYMid meet"
     {{ $attributes->merge(['class' => 'block w-full h-auto select-none '.$class]) }}>

    <defs>
        {{-- A ponta da seta e definida uma vez e reaproveitada por todas as
             setas do canvas. --}}
        <marker id="tactiboard-arrowhead" viewBox="0 0 10 10"
                refX="9" refY="5" markerWidth="5" markerHeight="5" orient="auto-start-reverse">
            <path d="M 0 0 L 10 5 L 0 10 z" fill="#facc15" />
        </marker>
    </defs>

    <rect x="{{ -$margin }}" y="{{ -$margin }}"
          width="{{ $width + $margin * 2 }}" height="{{ $height + $margin * 2 }}" fill="#2e7d46" />

    {{-- Faixas de corte do gramado: 10 faixas verticais de larguras iguais. --}}
    @for ($i = 0; $i < 10; $i += 2)
        <rect x="{{ $i * $width / 10 }}" y="0" width="{{ $width / 10 }}" height="{{ $height }}"
              fill="#ffffff" opacity="0.04" />
    @endfor

    <g fill="none" stroke="#ffffff" stroke-width="3" opacity="0.9">
        {{-- Linhas laterais e de fundo --}}
        <rect x="0" y="0" width="{{ $width }}" height="{{ $height }}" />

        {{-- Meio-campo, circulo central (raio 9,15 m) e marca central --}}
        <line x1="525" y1="0" x2="525" y2="680" />
        <circle cx="525" cy="340" r="91.5" />
        <circle cx="525" cy="340" r="4" fill="#ffffff" stroke="none" />

        {{-- Grande area (16,5 m x 40,32 m) e pequena area (5,5 m x 18,32 m) --}}
        <rect x="0" y="138.4" width="165" height="403.2" />
        <rect x="0" y="248.4" width="55" height="183.2" />
        <rect x="885" y="138.4" width="165" height="403.2" />
        <rect x="995" y="248.4" width="55" height="183.2" />

        {{-- Marca do penalti (11 m) e o arco de 9,15 m fora da grande area --}}
        <circle cx="110" cy="340" r="4" fill="#ffffff" stroke="none" />
        <path d="M 165,266.875 A 91.5 91.5 0 0 1 165,413.125" />
        <circle cx="940" cy="340" r="4" fill="#ffffff" stroke="none" />
        <path d="M 885,266.875 A 91.5 91.5 0 0 0 885,413.125" />

        {{-- Arcos de escanteio (1 m) --}}
        <path d="M 0,10 A 10 10 0 0 0 10,0" />
        <path d="M 1040,0 A 10 10 0 0 1 1050,10" />
        <path d="M 1050,670 A 10 10 0 0 0 1040,680" />
        <path d="M 10,680 A 10 10 0 0 0 0,670" />

        {{-- Traves (7,32 m), desenhadas fora da linha de fundo --}}
        <rect x="-20" y="303.4" width="20" height="73.2" />
        <rect x="1050" y="303.4" width="20" height="73.2" />
    </g>

    {{-- Os elementos da prancheta sao desenhados por cima do campo. --}}
    {{ $slot }}
</svg>
