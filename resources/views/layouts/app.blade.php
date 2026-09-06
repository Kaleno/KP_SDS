<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-100 text-slate-900">
        <div class="min-h-screen lg:flex">
            @include('layouts.navigation')

            <div class="flex-1 flex flex-col min-w-0 pb-20 lg:pb-0">
                <header class="bg-white border-b border-slate-200">
                    <div class="px-4 sm:px-6 lg:px-8 py-4">
                        @isset($header)
                            {{ $header }}
                        @else
                            <h1 class="text-lg font-semibold text-slate-800">{{ $title ?? config('app.name') }}</h1>
                        @endisset
                    </div>
                </header>

                <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6">
                    @if (session('status') && ! in_array(session('status'), ['profile-updated', 'password-updated', 'verification-link-sent'], true))
                        <div class="mb-4 rounded-lg bg-teal-50 text-teal-800 px-4 py-3 text-sm">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
