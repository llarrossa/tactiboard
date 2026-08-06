@props(['board'])

@php
    // O acesso publico exige prancheta publica *e* token (docs/03 §6.2). O
    // painel so mostra a URL quando as duas condicoes valem, para nao exibir
    // um link que ainda nao abre.
    $link = $board->sharedLinks->first();
    $isShared = $board->visibility === App\Enums\BoardVisibility::Public && $link !== null;
@endphp

<div class="mt-6 bg-white shadow-sm sm:rounded-lg p-6">
    <h3 class="font-medium text-gray-900">{{ __('Sharing') }}</h3>

    @if ($isShared)
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Anyone with this link can view the board. No account needed, and no one can edit it.') }}
        </p>

        {{-- Selecionar sempre, copiar quando der: navigator.clipboard exige
             contexto seguro e nao existe em HTTP fora de localhost. Sem o
             fallback, o botao nao faria nada e pareceria quebrado. --}}
        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center"
             x-data="{
                 copied: false,
                 copy() {
                     this.$refs.url.select();

                     navigator.clipboard?.writeText(this.$refs.url.value)
                         .then(() => {
                             this.copied = true;
                             setTimeout(() => this.copied = false, 2000);
                         })
                         .catch(() => {});
                 },
             }">
            {{-- A URL vem no `value`, nao por x-model: assim ela esta no HTML
                 servido e o usuario consegue le-la e copiar a mao mesmo antes
                 de o Alpine hidratar a pagina. --}}
            <input type="text" readonly x-ref="url"
                   value="{{ route('share.show', $link->token) }}"
                   aria-label="{{ __('Public link') }}"
                   x-on:focus="$event.target.select()"
                   class="w-full rounded-md border-gray-300 bg-gray-50 text-sm text-gray-700 shadow-sm focus:border-gray-500 focus:ring-gray-500" />

            <div class="flex items-center gap-3">
                {{-- Copiar e interacao local: nao ha estado de servidor
                     envolvido, entao fica no Alpine (docs/06 §10). --}}
                <button type="button" x-on:click="copy()"
                        class="inline-flex shrink-0 items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    {{ __('Copy link') }}
                </button>

                <span class="text-sm font-medium text-green-600" x-show="copied" x-cloak role="status" aria-live="polite">
                    {{ __('Link copied.') }}
                </span>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2">
            <form method="POST" action="{{ route('boards.share.destroy', $board) }}">
                @csrf
                @method('DELETE')

                <button type="submit" class="text-sm text-gray-600 underline hover:text-gray-900">
                    {{ __('Stop sharing') }}
                </button>
            </form>

            {{-- Deixar de compartilhar derruba o acesso, mas nao aposenta o
                 token: recompartilhar devolve a mesma URL (docs/03 §7.1). Este
                 e o caminho para quando o endereco em si vazou. --}}
            <button type="button"
                    x-on:click="$dispatch('open-modal', 'confirm-link-rotation')"
                    class="text-sm text-gray-600 underline hover:text-gray-900">
                {{ __('Generate a new link') }}
            </button>
        </div>

        <x-modal name="confirm-link-rotation" focusable maxWidth="md">
            <form method="POST" action="{{ route('boards.share.update', $board) }}" class="p-6">
                @csrf
                @method('PUT')

                <h2 class="text-lg font-medium text-gray-900">
                    {{ __('Generate a new link?') }}
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    {{ __('The current address stops working right away. Anyone you already sent it to will need the new link.') }}
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        {{ __('Cancel') }}
                    </x-secondary-button>

                    <x-danger-button>
                        {{ __('Generate a new link') }}
                    </x-danger-button>
                </div>
            </form>
        </x-modal>
    @else
        <p class="mt-1 text-sm text-gray-600">
            {{ __('This board is private. Share it to get a link that anyone can open, without an account.') }}
        </p>

        <form method="POST" action="{{ route('boards.share.store', $board) }}" class="mt-4">
            @csrf

            <button type="submit"
                    class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                {{ __('Share board') }}
            </button>
        </form>
    @endif
</div>
