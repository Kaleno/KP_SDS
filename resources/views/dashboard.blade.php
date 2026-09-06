<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="ui-section-title">{{ $roleLabel }}</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Beranda</h1>
            <p class="text-sm text-slate-500">Masuk sebagai {{ $roleLabel }}</p>
        </div>
    </x-slot>

    <div class="max-w-5xl space-y-5">
        <div class="relative overflow-hidden rounded-3xl bg-teal-950 px-5 py-6 text-white shadow-lift sm:px-8 sm:py-8">
            <div class="pointer-events-none absolute -right-8 -top-10 h-40 w-40 rounded-full bg-gold-400/20 blur-2xl"></div>
            <div class="pointer-events-none absolute bottom-0 right-10 h-24 w-24 rounded-full bg-teal-500/30 blur-xl"></div>
            <p class="text-[11px] uppercase tracking-[0.2em] text-gold-300">Assalamu'alaikum</p>
            <h2 class="mt-2 font-display text-3xl font-semibold text-balance">{{ Auth::user()->name }}</h2>
            <p class="mt-2 max-w-xl text-sm text-teal-100/80">
                @if ($isSuperAdmin)
                    Pemelihara teknis sistem. Kelola akun Ketua, lalu serahkan operasional harian kepada pimpinan.
                @elseif ($isKetua)
                    Ringkasan hari ini untuk pimpinan: halaqah, santri, setoran, dan kehadiran.
                @elseif ($isUstaz)
                    Buka sesi absensi hari ini, catat setoran, lalu pantau progress juz anggota.
                @else
                    Akun Anda sudah aktif. Portal pantau santri dan orang tua menyusul setelah operasional harian dipakai.
                @endif
            </p>
        </div>

        @if ($isSuperAdmin)
            <x-card>
                <p class="text-sm text-slate-600">
                    Anda pemelihara teknis sistem. Kelola akun Ketua di menu
                    <a href="{{ route('super-admin.ketua.index') }}" class="ui-link">Akun Ketua</a>.
                    Menu operasional harian tidak tersedia di role ini.
                </p>
            </x-card>
        @elseif ($isKetua)
            @if ($stats)
                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <div class="stat-card">
                        <div class="flex items-center justify-between">
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-800"><x-icon name="layers" /></span>
                        </div>
                        <p class="mt-4 font-display text-3xl font-semibold text-teal-950">{{ $stats['halaqah'] }}</p>
                        <p class="text-sm text-slate-500">Halaqah aktif</p>
                    </div>
                    <div class="stat-card">
                        <div class="flex items-center justify-between">
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gold-50 text-gold-700"><x-icon name="users" /></span>
                        </div>
                        <p class="mt-4 font-display text-3xl font-semibold text-teal-950">{{ $stats['santriAktif'] }}</p>
                        <p class="text-sm text-slate-500">Santri aktif</p>
                    </div>
                    <div class="stat-card">
                        <div class="flex items-center justify-between">
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-50 text-sky-700"><x-icon name="book" /></span>
                        </div>
                        <p class="mt-4 font-display text-3xl font-semibold text-teal-950">{{ $stats['setoranHariIni'] }}</p>
                        <p class="text-sm text-slate-500">Setoran hari ini</p>
                    </div>
                    <div class="stat-card border-rose-100 bg-rose-50/70">
                        <div class="flex items-center justify-between">
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-rose-700"><x-icon name="clipboard" /></span>
                        </div>
                        <p class="mt-4 font-display text-3xl font-semibold text-rose-800">{{ $stats['alfaHariIni'] }}</p>
                        <p class="text-sm text-rose-700">Alfa hari ini</p>
                    </div>
                </div>
            @endif
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <a class="ui-card group flex min-h-16 items-center justify-between px-5 py-4 transition hover:-translate-y-0.5 hover:shadow-lift" href="{{ route('laporan.progress.index') }}">
                    <span class="flex items-center gap-3 font-semibold text-teal-950"><span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-800"><x-icon name="chart" /></span>Progress juz</span>
                    <x-icon name="arrow-right" class="h-4 w-4 text-slate-400 group-hover:text-teal-800" />
                </a>
                <a class="ui-card group flex min-h-16 items-center justify-between px-5 py-4 transition hover:-translate-y-0.5 hover:shadow-lift" href="{{ route('laporan.attendance.index') }}">
                    <span class="flex items-center gap-3 font-semibold text-teal-950"><span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-800"><x-icon name="clipboard" /></span>Rekap absensi</span>
                    <x-icon name="arrow-right" class="h-4 w-4 text-slate-400 group-hover:text-teal-800" />
                </a>
                <a class="ui-card group flex min-h-16 items-center justify-between px-5 py-4 transition hover:-translate-y-0.5 hover:shadow-lift" href="{{ route('ops.attendance.index') }}">
                    <span class="flex items-center gap-3 font-semibold text-teal-950"><span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-800"><x-icon name="check-circle" /></span>Absensi hari ini</span>
                    <x-icon name="arrow-right" class="h-4 w-4 text-slate-400 group-hover:text-teal-800" />
                </a>
                <a class="ui-card group flex min-h-16 items-center justify-between px-5 py-4 transition hover:-translate-y-0.5 hover:shadow-lift" href="{{ route('ops.setoran.create') }}">
                    <span class="flex items-center gap-3 font-semibold text-teal-950"><span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gold-50 text-gold-700"><x-icon name="plus" /></span>Input setoran</span>
                    <x-icon name="arrow-right" class="h-4 w-4 text-slate-400 group-hover:text-teal-800" />
                </a>
            </div>
        @elseif ($isUstaz)
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <a class="ui-card group flex min-h-16 items-center justify-between px-5 py-4 transition hover:-translate-y-0.5 hover:shadow-lift" href="{{ route('ops.attendance.index') }}">
                    <span class="flex items-center gap-3 font-semibold text-teal-950"><span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-800"><x-icon name="check-circle" /></span>Absensi hari ini</span>
                    <x-icon name="arrow-right" class="h-4 w-4 text-slate-400 group-hover:text-teal-800" />
                </a>
                <a class="ui-card group flex min-h-16 items-center justify-between px-5 py-4 transition hover:-translate-y-0.5 hover:shadow-lift" href="{{ route('ops.setoran.create') }}">
                    <span class="flex items-center gap-3 font-semibold text-teal-950"><span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gold-50 text-gold-700"><x-icon name="plus" /></span>Input setoran</span>
                    <x-icon name="arrow-right" class="h-4 w-4 text-slate-400 group-hover:text-teal-800" />
                </a>
                <a class="ui-card group flex min-h-16 items-center justify-between px-5 py-4 transition hover:-translate-y-0.5 hover:shadow-lift" href="{{ route('laporan.progress.index') }}">
                    <span class="flex items-center gap-3 font-medium text-teal-950"><span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-800"><x-icon name="chart" /></span>Progress juz</span>
                    <x-icon name="arrow-right" class="h-4 w-4 text-slate-400 group-hover:text-teal-800" />
                </a>
                <a class="ui-card group flex min-h-16 items-center justify-between px-5 py-4 transition hover:-translate-y-0.5 hover:shadow-lift" href="{{ route('laporan.attendance.index') }}">
                    <span class="flex items-center gap-3 font-medium text-teal-950"><span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-800"><x-icon name="clipboard" /></span>Rekap absensi</span>
                    <x-icon name="arrow-right" class="h-4 w-4 text-slate-400 group-hover:text-teal-800" />
                </a>
            </div>
        @else
            <x-card>
                <p class="text-sm text-slate-600">
                    Akun Anda sudah aktif. Portal pantau santri dan orang tua menyusul setelah operasional harian dipakai.
                </p>
            </x-card>
        @endif
    </div>
</x-app-layout>
