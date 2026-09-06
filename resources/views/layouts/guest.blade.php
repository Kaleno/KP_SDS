<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0e221f">

        <title>{{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 antialiased">
        <div class="min-h-screen grid lg:grid-cols-2">
            <aside class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-teal-950 px-12 py-12 text-cream-50">
                <div class="absolute inset-0 opacity-40" style="background-image: radial-gradient(circle at 20% 20%, rgba(232,212,160,.18), transparent 35%), radial-gradient(circle at 80% 80%, rgba(90,168,147,.2), transparent 40%);"></div>
                <div class="relative">
                    <div class="flex items-center gap-3">
                        <x-application-logo class="h-11 w-11 text-teal-800" />
                        <div>
                            <p class="text-[11px] uppercase tracking-[0.22em] text-gold-300">KP SDS</p>
                            <p class="font-display text-lg text-white">Monitoring Hafalan</p>
                        </div>
                    </div>
                    <h1 class="mt-16 font-display text-4xl leading-tight text-white text-balance">
                        Hafalan, absensi, dan jadwal dalam satu ruang yang tenang.
                    </h1>
                    <p class="mt-5 max-w-md text-teal-100/80 leading-relaxed">
                        Dirancang untuk pesantren dan madrasah: ustaz mencatat di kelas, ketua memimpin dari pusat, orang tua memantau kemajuan anak.
                    </p>
                </div>
                <ul class="relative grid gap-4 text-sm text-teal-100/90">
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-8 w-8 items-center justify-center rounded-full bg-white/10 text-gold-300"><x-icon name="book" class="h-4 w-4" /></span>
                        <span>Setoran ayat dengan status lancar, ulang, atau perbaikan.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-8 w-8 items-center justify-center rounded-full bg-white/10 text-gold-300"><x-icon name="check-circle" class="h-4 w-4" /></span>
                        <span>Absensi sesi harian yang terikat jadwal halaqah.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-8 w-8 items-center justify-center rounded-full bg-white/10 text-gold-300"><x-icon name="chart" class="h-4 w-4" /></span>
                        <span>Progress 30 juz dihitung dari ayat unik yang sudah lancar.</span>
                    </li>
                </ul>
            </aside>

            <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10 sm:px-8">
                <div class="mb-8 text-center lg:hidden">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center">
                        <x-application-logo class="h-14 w-14" />
                    </div>
                    <p class="text-[11px] uppercase tracking-[0.22em] text-teal-700">Pesantren / Madrasah</p>
                    <h1 class="mt-1 font-display text-2xl font-semibold text-teal-950">Monitoring Hafalan</h1>
                    <p class="mt-1 text-sm text-slate-500">Absensi, setoran, dan jadwal santri</p>
                </div>

                <div class="w-full max-w-md ui-card p-6 sm:p-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
