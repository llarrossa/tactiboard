<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>

            <a href="{{ route('boards.create') }}"
               class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                {{ __('New board') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status') === 'board-deleted')
                <div class="mb-4 text-sm font-medium text-green-600">{{ __('Board deleted.') }}</div>
            @endif

            @if ($boards->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-gray-900 font-medium">{{ __('You have no boards yet.') }}</p>

                    <p class="mt-2 text-sm text-gray-600">
                        {{ __('Create your first tactical analysis to get started.') }}
                    </p>

                    <a href="{{ route('boards.create') }}"
                       class="mt-4 inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        {{ __('New board') }}
                    </a>
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($boards as $board)
                        <a href="{{ route('boards.show', $board) }}"
                           class="block bg-white shadow-sm sm:rounded-lg p-6 transition hover:shadow-md focus:outline-none focus:ring-2 focus:ring-gray-500">
                            <h3 class="font-semibold text-gray-900">{{ $board->title }}</h3>

                            <p class="mt-1 text-sm text-gray-600">{{ $board->category->label() }}</p>

                            <dl class="mt-4 text-xs text-gray-500 space-y-1">
                                <div class="flex justify-between gap-2">
                                    <dt>{{ __('Created at') }}</dt>
                                    <dd>{{ $board->created_at->format('d/m/Y') }}</dd>
                                </div>
                                <div class="flex justify-between gap-2">
                                    <dt>{{ __('Last updated') }}</dt>
                                    <dd>{{ $board->updated_at->format('d/m/Y') }}</dd>
                                </div>
                            </dl>
                        </a>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $boards->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
