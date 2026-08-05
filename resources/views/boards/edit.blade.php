<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit board') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('boards.update', $board) }}">
                    @csrf
                    @method('PUT')

                    @include('boards.partials.form-fields', ['board' => $board, 'categories' => $categories])

                    <div class="mt-6 flex items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>

                        <a href="{{ route('boards.show', $board) }}"
                           class="text-sm text-gray-600 underline hover:text-gray-900">
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900">{{ __('Delete board') }}</h3>

                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Deleting a board is permanent. This action cannot be undone.') }}
                </p>

                <x-danger-button
                    class="mt-4"
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal', 'confirm-board-deletion')"
                >{{ __('Delete board') }}</x-danger-button>

                <x-modal name="confirm-board-deletion" focusable>
                    <form method="POST" action="{{ route('boards.destroy', $board) }}" class="p-6">
                        @csrf
                        @method('DELETE')

                        <h2 class="text-lg font-medium text-gray-900">
                            {{ __('Are you sure you want to delete this board?') }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('Deleting a board is permanent. This action cannot be undone.') }}
                        </p>

                        <div class="mt-6 flex justify-end">
                            <x-secondary-button x-on:click="$dispatch('close')">
                                {{ __('Cancel') }}
                            </x-secondary-button>

                            <x-danger-button class="ms-3">
                                {{ __('Delete board') }}
                            </x-danger-button>
                        </div>
                    </form>
                </x-modal>
            </div>
        </div>
    </div>
</x-app-layout>
