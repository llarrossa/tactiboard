@props(['element', 'selected' => false, 'interactive' => true])

@php
    // O tipo vem do canvas gravado, que sempre passou pela validacao. Resolver
    // pelo enum garante que so exista componente para tipo conhecido.
    $type = App\Enums\CanvasElementType::from($element['type']);

    // Quem navega por teclado ou leitor de tela nao ve a cor nem a posicao da
    // peca, entao o rotulo diz qual elemento e — para o jogador, o que o
    // distingue dos outros e o lado e o numero.
    $label = match ($type) {
        App\Enums\CanvasElementType::Player => __(':side player number :number', [
            'side' => App\Enums\PlayerTeam::from($element['team'])->label(),
            'number' => $element['number'],
        ]),
        App\Enums\CanvasElementType::Text => __('Text: :content', ['content' => $element['content']]),
        default => $type->label(),
    };
@endphp

{{-- Fora do editor o grupo nao recebe as ligacoes de arrasto: offsetFor() e
     startDrag() vem do tactiboardCanvasDrag(), que so existe no BoardEditor.
     Mante-las na visualizacao publica nao quebraria o desenho, mas encheria o
     console de erro e sugeriria uma interacao que nao existe ali. --}}
@if ($interactive)
    {{-- O grupo e alcancavel por Tab e selecionavel por Enter ou espaco. O foco
         nao seleciona sozinho: percorrer o campo custaria uma ida ao servidor
         por peca, e os atalhos agem sobre a selecao, nao sobre o foco. --}}
    <g wire:key="canvas-element-{{ $element['id'] }}"
       tabindex="0"
       role="button"
       aria-label="{{ $label }}"
       aria-pressed="{{ $selected ? 'true' : 'false' }}"
       x-bind:transform="offsetFor(@js($element['id']))"
       x-on:pointerdown.prevent="startDrag($event, @js($element['id']))"
       x-on:keydown.enter.prevent="$wire.select(@js($element['id']))"
       x-on:keydown.space.prevent="$wire.select(@js($element['id']))"
       class="focus:outline-none focus-visible:[outline:3px_solid_#facc15]"
       {{-- `touch-action: none` vive na peca, e nao no campo inteiro: assim
            arrastar um jogador no celular move a peca, mas o dedo sobre a
            grama continua rolando a pagina. --}}
       style="cursor: grab; touch-action: none">
        @if ($selected && $type->isPositional())
            {{-- Anel de selecao. A seta nao usa: nela quem marca a selecao sao
                 as alcas nas duas pontas. --}}
            <circle cx="{{ $element['x'] }}" cy="{{ $element['y'] }}" r="27"
                    fill="none" stroke="#facc15" stroke-width="3" stroke-dasharray="7 5" />
        @endif

        <x-dynamic-component :component="'canvas.'.$type->value" :element="$element" :selected="$selected" />
    </g>
@else
    <g>
        <x-dynamic-component :component="'canvas.'.$type->value" :element="$element" :selected="false" />
    </g>
@endif
