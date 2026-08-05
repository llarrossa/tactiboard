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
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('status') === 'board-created')
                <div class="mb-4 text-sm font-medium text-green-600">{{ __('Board created.') }}</div>
            @elseif (session('status') === 'board-updated')
                <div class="mb-4 text-sm font-medium text-green-600">{{ __('Board updated.') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Category') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $board->category->label() }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Description') }}</dt>
                        <dd class="mt-1 text-gray-900 whitespace-pre-line">
                            {{ $board->description ?: __('No description.') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Visibility') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $board->visibility->label() }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Last updated') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $board->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            {{-- O editor tático entra na Fase 3. Até lá esta tela mostra apenas
                 os dados descritivos da prancheta. --}}

            <div class="mt-6">
                <a href="{{ route('dashboard') }}"
                   class="text-sm text-gray-600 underline hover:text-gray-900">
                    {{ __('Back to dashboard') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
