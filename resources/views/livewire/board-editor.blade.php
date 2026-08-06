<div class="bg-white shadow-sm sm:rounded-lg p-4 sm:p-6"
     x-data="{ saved: false, ...tactiboardCanvasDrag() }"
     x-on:canvas-saved.window="saved = true; setTimeout(() => saved = false, 2500)"
     x-on:pointermove.window="onMove($event)"
     x-on:pointerup.window="endDrag()">

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <x-editor-toolbar />

        <div class="flex items-center gap-3">
            <span class="text-sm" role="status" aria-live="polite">
                <span x-show="saved" x-cloak class="font-medium text-green-600">
                    {{ __('Board saved.') }}
                </span>

                <span wire:loading wire:target="save" class="text-gray-500">
                    {{ __('Saving...') }}
                </span>
            </span>

            <button type="button"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50">
                {{ __('Save board') }}
            </button>
        </div>
    </div>

    @error('elements')
        <p class="mb-4 text-sm text-red-600">{{ $message }}</p>
    @enderror

    {{-- As regras do canvas usam curingas (elements.*.x), entao os erros dos
         elementos chegam com o indice no nome. Mostrar a primeira mensagem
         basta: o usuario nao escolhe o indice, ele corrige o desenho. --}}
    @if ($errors->any() && ! $errors->has('elements'))
        <p class="mb-4 text-sm text-red-600">{{ $errors->first() }}</p>
    @endif

    {{-- `touch-none` desliga o gesto de rolagem sobre o campo: sem isso, no
         celular arrastar um jogador rola a pagina em vez de mover a peca. --}}
    <x-field x-ref="field" class="touch-none"
             x-on:pointerdown.self="$wire.select(null)">
        @foreach ($drawable as $element)
            <x-canvas.element :element="$element" :selected="$selectedId === $element['id']" />
        @endforeach
    </x-field>

    @if ($selectedIndex !== null)
        <x-element-properties :element="$elements[$selectedIndex]" :index="$selectedIndex" />
    @endif
</div>
