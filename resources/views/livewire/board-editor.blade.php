<div class="bg-white shadow-sm sm:rounded-lg p-4 sm:p-6"
     x-data="{ saved: false, ...tactiboardCanvasDrag(), ...tactiboardEditorShortcuts() }"
     x-on:canvas-saved.window="saved = true; setTimeout(() => saved = false, 2500)"
     x-on:pointermove.window="onMove($event)"
     x-on:pointerup.window="endDrag()"
     x-on:pointercancel.window="cancelDrag()"
     x-on:keydown.window="onShortcut($event)">

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <x-editor-toolbar :count="count($elements)" />

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

    {{-- O gesto de rolagem so e desligado sobre as pecas (ver
         canvas/element.blade.php). Desligar no campo inteiro impediria o
         usuario de rolar a pagina com o dedo sobre a grama. --}}
    <x-field x-ref="field"
             x-on:pointerdown.self="$wire.select(null)">
        @foreach ($drawable as $element)
            <x-canvas.element :element="$element" :selected="$selectedId === $element['id']" />
        @endforeach
    </x-field>

    @if ($selectedIndex !== null)
        <x-element-properties :element="$elements[$selectedIndex]" :index="$selectedIndex" />
    @endif

    {{-- Nenhum atalho faz algo que a interface nao ofereca por botao: eles
         encurtam o caminho de quem ja conhece o editor, nao escondem funcao. --}}
    <details class="mt-4 text-xs text-gray-500">
        <summary class="cursor-pointer font-medium text-gray-600 hover:text-gray-900">
            {{ __('Keyboard shortcuts') }}
        </summary>

        @php
            $key = 'rounded border border-gray-300 bg-gray-50 px-1.5 py-0.5 font-sans text-[11px] text-gray-700';
        @endphp

        <ul class="mt-2 grid gap-1 sm:grid-cols-2 lg:grid-cols-3">
            <li><kbd class="{{ $key }}">Ctrl / &#8984;</kbd> + <kbd class="{{ $key }}">S</kbd> &mdash; {{ __('Save board') }}</li>
            <li><kbd class="{{ $key }}">Ctrl / &#8984;</kbd> + <kbd class="{{ $key }}">D</kbd> &mdash; {{ __('Duplicate') }}</li>
            <li><kbd class="{{ $key }}">Delete</kbd> &mdash; {{ __('Remove element') }}</li>
            <li><kbd class="{{ $key }}">Esc</kbd> &mdash; {{ __('Deselect') }}</li>
            <li><kbd class="{{ $key }}">&larr; &uarr; &darr; &rarr;</kbd> &mdash; {{ __('Move the selected element') }}</li>
            <li><kbd class="{{ $key }}">Shift</kbd> + <kbd class="{{ $key }}">&larr; &uarr; &darr; &rarr;</kbd> &mdash; {{ __('Move in fine steps') }}</li>
            <li><kbd class="{{ $key }}">Tab</kbd> + <kbd class="{{ $key }}">Enter</kbd> &mdash; {{ __('Reach an element and select it') }}</li>
        </ul>
    </details>

    <x-modal name="confirm-clear-canvas" focusable maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Clear the whole field?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Every element leaves the field. Nothing is saved until you save the board.') }}
            </p>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                {{-- type=button explicito: o x-danger-button nasce como submit,
                     e aqui nao existe formulario para enviar. --}}
                <x-danger-button type="button" wire:click="clearCanvas" x-on:click="$dispatch('close')">
                    {{ __('Clear field') }}
                </x-danger-button>
            </div>
        </div>
    </x-modal>
</div>
