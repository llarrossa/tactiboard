@props(['element'])

@php
    // O tipo vem do canvas gravado, que sempre passou pela validacao. Resolver
    // pelo enum garante que so exista componente para tipo conhecido.
    $type = App\Enums\CanvasElementType::from($element['type']);
@endphp

<g wire:key="canvas-element-{{ $element['id'] }}">
    <x-dynamic-component :component="'canvas.'.$type->value" :element="$element" />
</g>
