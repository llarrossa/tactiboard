@props(['element', 'selected' => false])

@php
    // O tipo vem do canvas gravado, que sempre passou pela validacao. Resolver
    // pelo enum garante que so exista componente para tipo conhecido.
    $type = App\Enums\CanvasElementType::from($element['type']);
@endphp

<g wire:key="canvas-element-{{ $element['id'] }}"
   x-bind:transform="offsetFor(@js($element['id']))"
   x-on:pointerdown.prevent="startDrag($event, @js($element['id']))"
   style="cursor: grab">

    @if ($selected && $type->isPositional())
        {{-- Anel de selecao. A seta nao usa: nela quem marca a selecao sao as
             alcas nas duas pontas. --}}
        <circle cx="{{ $element['x'] }}" cy="{{ $element['y'] }}" r="27"
                fill="none" stroke="#facc15" stroke-width="3" stroke-dasharray="7 5" />
    @endif

    <x-dynamic-component :component="'canvas.'.$type->value" :element="$element" :selected="$selected" />
</g>
