@props(['element', 'index'])

{{--
    Painel de propriedades do elemento selecionado.

    Os campos escrevem direto no caminho do elemento dentro de `elements`, o
    que mantem uma unica copia do estado no componente. A validacao acontece no
    `updated()` do BoardEditor, com as mesmas regras do salvamento.
--}}

@php
    $type = App\Enums\CanvasElementType::from($element['type']);
    $field = 'block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm';
@endphp

<div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
    {{-- No celular os campos e as acoes empilham; a partir do `sm` voltam a
         ficar lado a lado, com as acoes empurradas para a direita. --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end">
        <div>
            <span class="block text-xs font-medium uppercase tracking-wide text-gray-500">
                {{ __('Selected element') }}
            </span>
            <span class="mt-1 block font-semibold text-gray-900">{{ $type->label() }}</span>
        </div>

        @if ($type === App\Enums\CanvasElementType::Player)
            <div>
                <label for="element-number" class="block text-xs font-medium text-gray-600">
                    {{ __('Number') }}
                </label>
                <input id="element-number" type="number" min="1" max="99"
                       wire:model.blur="elements.{{ $index }}.number"
                       class="{{ $field }} mt-1 w-24">
            </div>

            <div>
                <label for="element-team" class="block text-xs font-medium text-gray-600">
                    {{ __('Side') }}
                </label>
                <select id="element-team" wire:model.blur="elements.{{ $index }}.team"
                        class="{{ $field }} mt-1">
                    @foreach (App\Enums\PlayerTeam::cases() as $side)
                        <option value="{{ $side->value }}">{{ $side->label() }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if ($type === App\Enums\CanvasElementType::Text)
            <div class="w-full sm:min-w-64 sm:flex-1">
                <label for="element-content" class="block text-xs font-medium text-gray-600">
                    {{ __('Content') }}
                </label>
                <input id="element-content" type="text"
                       maxlength="{{ App\Rules\CanvasRules::MAX_TEXT_LENGTH }}"
                       wire:model.blur="elements.{{ $index }}.content"
                       class="{{ $field }} mt-1">
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-2 sm:ms-auto">
            <button type="button" wire:click="select(null)"
                    class="rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">
                {{ __('Deselect') }}
            </button>

            <button type="button" wire:click="duplicateElement('{{ $element['id'] }}')"
                    class="rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">
                {{ __('Duplicate') }}
            </button>

            <button type="button" wire:click="removeElement('{{ $element['id'] }}')"
                    class="rounded-md bg-red-600 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-red-500">
                {{ __('Remove element') }}
            </button>
        </div>
    </div>

    @if ($type === App\Enums\CanvasElementType::Arrow)
        <p class="mt-3 text-xs text-gray-500">
            {{ __('Drag the handles at each end to change the direction.') }}
        </p>
    @endif
</div>
