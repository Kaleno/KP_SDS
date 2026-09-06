<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center px-4 py-8 bg-teal-800">
            <div class="text-center mb-6">
                <p class="text-teal-100 text-sm uppercase tracking-wide">Pesantren / Madrasah</p>
                <h1 class="text-2xl font-semibold text-white mt-1">Monitoring Hafalan</h1>
                <p class="text-teal-200 text-sm mt-1">Absensi, setoran, dan jadwal santri</p>
            </div>

            <div class="w-full sm:max-w-md px-6 py-6 bg-white shadow-sm rounded-xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
