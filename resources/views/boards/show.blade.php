<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $board->title }}
            </h2>

            <a href="{{ route('boards.edit', $board) }}"
               class="text-sm text-gray-600 underline hover:text-gray-900">
                {{ __('Edit board') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status') === 'board-created')
                <div class="mb-4 text-sm font-medium text-green-600">{{ __('Board created.') }}</div>
            @elseif (session('status') === 'board-updated')
                <div class="mb-4 text-sm font-medium text-green-600">{{ __('Board updated.') }}</div>
            @elseif (session('status') === 'board-shared')
                <div class="mb-4 text-sm font-medium text-green-600">{{ __('Board shared.') }}</div>
            @elseif (session('status') === 'board-unshared')
                <div class="mb-4 text-sm font-medium text-green-600">{{ __('Board is private again.') }}</div>
            @endif

            {{-- Esta tela e o editor: a prancheta e o campo. O formulario de
                 nome, descricao e categoria continua em boards.edit. --}}
            <livewire:board-editor :board="$board" />

            <div class="mt-6 bg-white shadow-sm sm:rounded-lg p-6">
                <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Category') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $board->category->label() }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Visibility') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $board->visibility->label() }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Last updated') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $board->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Description') }}</dt>
                        <dd class="mt-1 text-gray-900 whitespace-pre-line">
                            {{ $board->description ?: __('No description.') }}
                        </dd>
                    </div>
                </dl>
            </div>

            @include('boards.partials.share-panel', ['board' => $board])

            <div class="mt-6">
                <a href="{{ route('dashboard') }}"
                   class="text-sm text-gray-600 underline hover:text-gray-900">
                    {{ __('Back to dashboard') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
