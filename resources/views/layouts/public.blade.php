<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ?? config('app.name', 'TactiBoard') }}</title>

        {{-- Uma prancheta compartilhada nao deve entrar em buscador: quem tem
             o link ja tem o acesso, e indexar tornaria publico o que o dono
             escolheu enviar para pessoas especificas. --}}
        <meta name="robots" content="noindex, nofollow">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    {{--
        Layout do visitante anonimo. Nao ha navbar, nao ha sessao e nao ha
        Livewire: a pagina e estatica de proposito. O guest.blade.php nao serve
        aqui — ele e o cartao estreito das telas de autenticacao, e o campo
        precisa de largura.
    --}}
    <body class="font-sans text-gray-900 antialiased bg-gray-100">
        <div class="min-h-screen">
            <header class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center gap-3">
                    <a href="{{ url('/') }}" class="flex items-center gap-3">
                        <x-application-logo class="w-9 h-9 fill-current text-gray-500" />

                        <span class="font-semibold text-gray-800">{{ config('app.name', 'TactiBoard') }}</span>
                    </a>
                </div>
            </header>

            <main class="py-8 sm:py-12">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
