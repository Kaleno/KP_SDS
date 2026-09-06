<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Beranda</h1>
        <p class="text-sm text-slate-500">Masuk sebagai {{ $roleLabel }}</p>
    </x-slot>

    <div class="max-w-3xl space-y-4">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-slate-800">Assalamu'alaikum, <span class="font-semibold">{{ Auth::user()->name }}</span>.</p>

            @if ($isSuperAdmin)
                <p class="mt-3 text-sm text-slate-600">
                    Anda pemelihara teknis sistem. Kelola akun Ketua di menu
                    <a href="{{ route('super-admin.ketua.index') }}" class="text-teal-700 font-medium underline">Akun Ketua</a>.
                    Menu operasional harian tidak tersedia di role ini.
                </p>
            @elseif ($isKetua)
                @if ($stats)
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <p class="text-2xl font-semibold text-slate-800">{{ $stats['halaqah'] }}</p>
                            <p class="text-sm text-slate-500">Halaqah aktif</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <p class="text-2xl font-semibold text-slate-800">{{ $stats['santriAktif'] }}</p>
                            <p class="text-sm text-slate-500">Santri aktif</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <p class="text-2xl font-semibold text-slate-800">{{ $stats['setoranHariIni'] }}</p>
                            <p class="text-sm text-slate-500">Setoran hari ini</p>
                        </div>
                        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3">
                            <p class="text-2xl font-semibold text-rose-800">{{ $stats['alfaHariIni'] }}</p>
                            <p class="text-sm text-rose-700">Alfa hari ini</p>
                        </div>
                    </div>
                @endif
                <div class="mt-4 grid grid-cols-1 gap-3 text-sm">
                    <a class="rounded-xl border border-slate-200 px-4 py-4 min-h-14 flex items-center font-medium hover:border-teal-600" href="{{ route('laporan.progress.index') }}">Progress juz</a>
                    <a class="rounded-xl border border-slate-200 px-4 py-4 min-h-14 flex items-center font-medium hover:border-teal-600" href="{{ route('laporan.attendance.index') }}">Rekap absensi</a>
                    <a class="rounded-xl border border-slate-200 px-4 py-4 min-h-14 flex items-center font-medium hover:border-teal-600" href="{{ route('ops.attendance.index') }}">Absensi hari ini</a>
                    <a class="rounded-xl border border-slate-200 px-4 py-4 min-h-14 flex items-center font-medium hover:border-teal-600" href="{{ route('ops.setoran.create') }}">Input setoran</a>
                </div>
            @elseif ($isUstaz)
                <p class="mt-3 text-sm text-slate-600">Buka sesi absensi hari ini, catat setoran, lalu pantau progress juz anggota.</p>
                <div class="mt-4 grid grid-cols-1 gap-3 text-sm">
                    <a class="rounded-xl border border-slate-200 px-4 py-4 min-h-14 flex items-center font-semibold hover:border-teal-600" href="{{ route('ops.attendance.index') }}">Absensi hari ini</a>
                    <a class="rounded-xl border border-slate-200 px-4 py-4 min-h-14 flex items-center font-semibold hover:border-teal-600" href="{{ route('ops.setoran.create') }}">Input setoran</a>
                    <a class="rounded-xl border border-slate-200 px-4 py-4 min-h-14 flex items-center hover:border-teal-600" href="{{ route('laporan.progress.index') }}">Progress juz</a>
                    <a class="rounded-xl border border-slate-200 px-4 py-4 min-h-14 flex items-center hover:border-teal-600" href="{{ route('laporan.attendance.index') }}">Rekap absensi</a>
                </div>
            @else
                <p class="mt-3 text-sm text-slate-600">
                    Akun Anda sudah aktif. Portal pantau santri dan orang tua menyusul setelah operasional harian dipakai.
                </p>
            @endif
        </div>
    </div>
</x-app-layout>
