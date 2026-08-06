<x-public-layout :title="$board->title.' — '.config('app.name', 'TactiBoard')">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-4 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="font-semibold text-xl text-gray-800 leading-tight">{{ $board->title }}</h1>

                    <p class="mt-1 text-sm text-gray-600">{{ $board->category->label() }}</p>
                </div>

                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">
                    {{ __('View only') }}
                </span>
            </div>

            {{-- Sem `touch-none` e sem ligacoes de arrasto: aqui o campo e uma
                 imagem, e rolar a pagina com o dedo sobre ele deve rolar. --}}
            <x-field class="mt-4">
                @foreach ($elements as $element)
                    <x-canvas.element :element="$element" :interactive="false" />
                @endforeach
            </x-field>

            @if ($board->description)
                <div class="mt-6">
                    <h2 class="text-sm font-medium text-gray-500">{{ __('Description') }}</h2>

                    <p class="mt-1 text-gray-900 whitespace-pre-line">{{ $board->description }}</p>
                </div>
            @endif

            <p class="mt-6 text-xs text-gray-500">
                {{ __('Shared through TactiBoard. Last updated on :date.', ['date' => $board->updated_at->format('d/m/Y')]) }}
            </p>
        </div>
    </div>
</x-public-layout>
