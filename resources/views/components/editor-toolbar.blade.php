{{--
    Barra de ferramentas do editor.

    E um componente Blade, nao Livewire: ela nao guarda estado proprio, apenas
    dispara acoes no BoardEditor que a envolve. Um componente Livewire aqui
    adicionaria um ciclo de vida e um canal de eventos sem necessidade real
    (docs/04 secao 20).
--}}

@props(['count' => 0])

@php
    $button = 'inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 '
        .'text-xs font-semibold text-gray-700 transition hover:bg-gray-50 '
        .'focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-1 disabled:opacity-50';
@endphp

<div class="flex flex-wrap items-center gap-x-4 gap-y-2">
    <div class="flex flex-wrap items-center gap-2" role="group" aria-label="{{ __('Add elements') }}">
        <button type="button" wire:click="addPlayer('home')" class="{{ $button }}">
            <span class="h-3 w-3 rounded-full bg-blue-700" aria-hidden="true"></span>
            {{ __('Own team') }}
        </button>

        <button type="button" wire:click="addPlayer('away')" class="{{ $button }}">
            <span class="h-3 w-3 rounded-full bg-red-600" aria-hidden="true"></span>
            {{ __('Opponent') }}
        </button>

        <button type="button" wire:click="addBall" class="{{ $button }}">
            <span class="h-3 w-3 rounded-full border border-gray-900 bg-white" aria-hidden="true"></span>
            {{ __('Ball') }}
        </button>

        <button type="button" wire:click="addCone" class="{{ $button }}">
            <span class="h-0 w-0 border-x-[6px] border-b-[10px] border-x-transparent border-b-orange-500" aria-hidden="true"></span>
            {{ __('Cone') }}
        </button>

        <button type="button" wire:click="addText" class="{{ $button }}">
            <span class="font-bold text-gray-900" aria-hidden="true">T</span>
            {{ __('Text') }}
        </button>

        <button type="button" wire:click="addArrow" class="{{ $button }}">
            <span class="font-bold text-gray-900" aria-hidden="true">&rarr;</span>
            {{ __('Arrow') }}
        </button>
    </div>

    <div class="flex flex-wrap items-center gap-2" role="group" aria-label="{{ __('Field') }}">
        {{-- Limpar o campo nao grava: a confirmacao existe porque a acao apaga
             a jogada inteira da tela, e so o recarregamento a traria de volta. --}}
        <button type="button"
                x-on:click="$dispatch('open-modal', 'confirm-clear-canvas')"
                @disabled($count === 0)
                class="{{ $button }}">
            {{ __('Clear field') }}
        </button>
    </div>
</div>
