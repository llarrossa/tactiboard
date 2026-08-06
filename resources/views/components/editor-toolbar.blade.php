{{--
    Barra de ferramentas do editor.

    E um componente Blade, nao Livewire: ela nao guarda estado proprio, apenas
    dispara acoes no BoardEditor que a envolve. Um componente Livewire aqui
    adicionaria um ciclo de vida e um canal de eventos sem necessidade real
    (docs/04 secao 20).

    Duplicar e remover ficam no painel de propriedades, e nao aqui: sao acoes
    sobre o elemento selecionado, e repeti-las na barra daria dois caminhos
    para a mesma coisa sem informacao nova.
--}}

@props(['count' => 0])

@php
    $max = App\Rules\CanvasRules::MAX_ELEMENTS;
    $isFull = $count >= $max;

    $button = 'inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 '
        .'text-xs font-semibold text-gray-700 transition hover:bg-gray-50 '
        .'focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-1 '
        .'disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-white';

    $groupLabel = 'text-[11px] font-semibold uppercase tracking-wide text-gray-400';
@endphp

<div class="flex flex-wrap items-start gap-x-6 gap-y-3">
    <div>
        <span id="toolbar-add-label" class="{{ $groupLabel }}">{{ __('Add elements') }}</span>

        <div class="mt-1 flex flex-wrap items-center gap-2" role="group" aria-labelledby="toolbar-add-label">
            <button type="button" wire:click="addPlayer('home')" @disabled($isFull) class="{{ $button }}">
                <span class="h-3 w-3 rounded-full bg-blue-700" aria-hidden="true"></span>
                {{ __('Own team') }}
            </button>

            <button type="button" wire:click="addPlayer('away')" @disabled($isFull) class="{{ $button }}">
                <span class="h-3 w-3 rounded-full bg-red-600" aria-hidden="true"></span>
                {{ __('Opponent') }}
            </button>

            <button type="button" wire:click="addBall" @disabled($isFull) class="{{ $button }}">
                <span class="h-3 w-3 rounded-full border border-gray-900 bg-white" aria-hidden="true"></span>
                {{ __('Ball') }}
            </button>

            <button type="button" wire:click="addCone" @disabled($isFull) class="{{ $button }}">
                <span class="h-0 w-0 border-x-[6px] border-b-[10px] border-x-transparent border-b-orange-500" aria-hidden="true"></span>
                {{ __('Cone') }}
            </button>

            <button type="button" wire:click="addText" @disabled($isFull) class="{{ $button }}">
                <span class="font-bold text-gray-900" aria-hidden="true">T</span>
                {{ __('Text') }}
            </button>

            <button type="button" wire:click="addArrow" @disabled($isFull) class="{{ $button }}">
                <span class="font-bold text-gray-900" aria-hidden="true">&rarr;</span>
                {{ __('Arrow') }}
            </button>
        </div>
    </div>

    <div>
        <span id="toolbar-field-label" class="{{ $groupLabel }}">{{ __('Field') }}</span>

        <div class="mt-1 flex flex-wrap items-center gap-3" role="group" aria-labelledby="toolbar-field-label">
            {{-- Limpar o campo nao grava: a confirmacao existe porque a acao
                 apaga a jogada inteira da tela, e so o recarregamento a traria
                 de volta. --}}
            <button type="button"
                    x-on:click="$dispatch('open-modal', 'confirm-clear-canvas')"
                    @disabled($count === 0)
                    class="{{ $button }}">
                {{ __('Clear field') }}
            </button>

            {{-- O limite aparece antes de ser atingido: descobri-lo por
                 mensagem de erro, com a jogada ja montada, e tarde demais. --}}
            <span class="text-xs {{ $isFull ? 'font-semibold text-red-600' : 'text-gray-500' }}"
                  role="status" aria-live="polite">
                {{ __(':count of :max elements', ['count' => $count, 'max' => $max]) }}
            </span>
        </div>
    </div>
</div>
