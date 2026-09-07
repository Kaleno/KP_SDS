<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="ui-section-title">{{ $roleLabel }}</p>
                <h1 class="font-display text-2xl font-semibold text-teal-950">Beranda</h1>
                <p class="text-sm text-slate-500">Masuk sebagai {{ $roleLabel }}</p>
            </div>
            @if ($overview)
                <p class="text-sm text-slate-500">{{ $overview['dayLabel'] }}, {{ $overview['todayLabel'] }}</p>
            @endif
        </div>
    </x-slot>

    <div class="max-w-6xl space-y-5">
        <div class="relative overflow-hidden rounded-3xl bg-teal-950 px-5 py-6 text-white shadow-lift sm:px-8 sm:py-8">
            <div class="pointer-events-none absolute -right-8 -top-10 h-40 w-40 rounded-full bg-gold-400/20 blur-2xl"></div>
            <div class="pointer-events-none absolute bottom-0 right-10 h-24 w-24 rounded-full bg-teal-500/30 blur-xl"></div>
            <p class="text-[11px] uppercase tracking-[0.2em] text-gold-300">Assalamu'alaikum</p>
            <h2 class="mt-2 font-display text-3xl font-semibold text-balance">{{ Auth::user()->name }}</h2>
            <p class="mt-2 max-w-xl text-sm text-teal-100/80">
                @if ($isSuperAdmin)
                    Pemelihara teknis sistem. Kelola akun Ketua, lalu serahkan operasional harian kepada pimpinan.
                @elseif ($isKetua)
                    Ringkasan hari ini untuk pimpinan: sesi, kehadiran, setoran, dan halaqah yang perlu perhatian.
                @elseif ($isUstaz)
                    Fokus ke sesi hari ini: buka absensi, catat setoran, lalu tindaklanjuti santri yang belum lancar.
                @else
                    Akun Anda sudah aktif. Portal pantau santri dan orang tua menyusul setelah operasional harian dipakai.
                @endif
            </p>
            @if ($overview)
                <p class="mt-4 text-xs text-teal-100/70">
                    {{ $overview['year']?->name ?? 'Belum ada tahun ajaran aktif' }}
                    · {{ $overview['dayLabel'] }} {{ $overview['todayLabel'] }}
                </p>
            @endif
        </div>

        @if ($isSuperAdmin)
            <x-card>
                <p class="text-sm text-slate-600">
                    Anda pemelihara teknis sistem. Kelola akun Ketua di menu
                    <a href="{{ route('super-admin.ketua.index') }}" class="ui-link">Akun Ketua</a>.
                    Menu operasional harian tidak tersedia di role ini.
                </p>
            </x-card>
        @elseif ($isKetua || $isUstaz)
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
