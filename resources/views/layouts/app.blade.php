<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @include('partials.pwa')

        <title>{{ $title ?? config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-800">
        <div
            class="min-h-screen lg:flex lg:h-screen lg:overflow-hidden"
            x-data="{ menuOpen: false }"
            x-init="
                const nav = $refs.sidebarNav;
                if (nav) {
                    nav.scrollTop = Number(sessionStorage.getItem('sidebar-nav-scroll') || 0);
                    nav.addEventListener('scroll', () => sessionStorage.setItem('sidebar-nav-scroll', String(nav.scrollTop)));
                }
            "
        >
            @include('layouts.navigation')

            <div class="ui-content-scroll flex min-w-0 flex-1 flex-col safe-bottom lg:pb-0">
                <header class="sticky top-0 z-10 hidden lg:block">
                    <div class="mx-4 mt-4 sm:mx-6 lg:mx-8">
                        <div class="ui-card max-w-6xl px-5 py-4">
                            @isset($header)
                                {{ $header }}
                            @else
                                <h1 class="font-display text-xl font-semibold text-teal-950">{{ $title ?? config('app.name') }}</h1>
                            @endisset
                        </div>
                    </div>
                </header>

                <header class="px-4 pb-1 pt-[calc(4.75rem+env(safe-area-inset-top))] lg:hidden">
                    @isset($header)
                        {{ $header }}
                    @else
                        <h1 class="font-display text-xl font-semibold text-teal-950">{{ $title ?? config('app.name') }}</h1>
                    @endisset
                </header>

                <main class="flex-1 px-4 sm:px-6 lg:px-8 py-5 lg:py-6">
                    @if (session('status') && ! in_array(session('status'), ['profile-updated', 'password-updated', 'verification-link-sent'], true))
                        <div class="mb-4 rounded-2xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900 shadow-soft">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
