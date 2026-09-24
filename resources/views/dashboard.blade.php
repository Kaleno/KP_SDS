<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="ui-section-title">{{ $roleLabel }}</p>
                <h1 class="font-display text-2xl font-semibold text-teal-950">Beranda</h1>
            </div>
            @if ($overview)
                <p class="text-sm text-slate-500">{{ $overview['dayLabel'] }}, {{ $overview['todayLabel'] }}</p>
            @endif
        </div>
    </x-slot>

    <div class="max-w-6xl space-y-5">
        <div class="relative overflow-hidden rounded-3xl bg-teal-950 px-5 py-5 text-white shadow-lift sm:px-8 sm:py-6">
            <div class="pointer-events-none absolute -right-8 -top-10 h-40 w-40 rounded-full bg-gold-400/20 blur-2xl"></div>
            <p class="text-[11px] uppercase tracking-[0.2em] text-gold-300">Assalamu'alaikum</p>
            <h2 class="mt-1 font-display text-2xl font-semibold text-balance sm:text-3xl">{{ Auth::user()->name }}</h2>
            @if ($isSuperAdmin)
                <p class="mt-2 max-w-xl text-sm text-teal-100/80">
                    Pemelihara teknis sistem. Kelola akun Ketua, lalu serahkan operasional harian kepada pimpinan.
                </p>
            @elseif ($overview)
                <p class="mt-2 text-sm text-teal-100/80">
                    {{ $overview['dayLabel'] }}, {{ $overview['todayLabel'] }}
                </p>
            @endif
        </div>

        @if ($isKetua && ($readiness['ready'] ?? true) === false)
            @include('dashboard.partials.readiness')
        @endif

        @if ($isSuperAdmin)
            <x-card>
                <p class="text-sm text-slate-600">
                    Anda pemelihara teknis sistem. Kelola akun Ketua di menu
                    <a href="{{ route('super-admin.ketua.index') }}" class="ui-link">Akun Ketua</a>.
                    Menu operasional harian tidak tersedia di role ini.
                </p>
            </x-card>
        @elseif ($isKetua)
            @include('dashboard.partials.ketua')
        @elseif ($isUstaz)
            @include('dashboard.partials.operator')
        @else
            <x-card>
                <p class="text-sm text-slate-600">
                    Akun Anda sudah aktif. Portal pantau santri dan orang tua menyusul setelah operasional harian dipakai.
                </p>
            </x-card>
        @endif
    </div>
</x-app-layout>
