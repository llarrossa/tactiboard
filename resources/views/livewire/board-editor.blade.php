<div class="bg-white shadow-sm sm:rounded-lg p-4 sm:p-6"
     x-data="{ saved: false }"
     @canvas-saved.window="saved = true; setTimeout(() => saved = false, 2500)">

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="text-sm" role="status" aria-live="polite">
            <span x-show="saved" x-cloak class="font-medium text-green-600">
                {{ __('Board saved.') }}
            </span>

            <span wire:loading wire:target="save" class="text-gray-500">
                {{ __('Saving...') }}
            </span>
        </div>

        <button type="button"
                wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save"
                class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50">
            {{ __('Save board') }}
        </button>
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

    <x-field />
</div>
