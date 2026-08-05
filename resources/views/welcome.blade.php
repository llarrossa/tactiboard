<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900">
        <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100 px-6 py-12">
            <x-application-logo class="w-20 h-20 text-gray-500" />

            <h1 class="mt-6 text-3xl font-semibold">{{ config('app.name') }}</h1>

            <p class="mt-3 max-w-md text-center text-gray-600">
                {{ __('Create, organize and share football tactical analyses.') }}
            </p>

            @if (Route::has('login'))
                <div class="mt-8 flex items-center gap-4">
                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                            {{ __('Dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                            {{ __('Log in') }}
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="rounded-md px-4 py-2 text-sm font-semibold text-gray-700 underline transition hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                {{ __('Register') }}
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </div>
    </body>
</html>
