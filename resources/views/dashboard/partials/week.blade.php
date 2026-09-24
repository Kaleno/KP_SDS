@php
    $week = $overview['week'];
@endphp

<section class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <h2 class="ui-section-title">Minggu ini</h2>
        <a href="{{ route('laporan.attendance.index', ['date_from' => $week['from'], 'date_to' => $week['to']]) }}" class="text-sm font-semibold text-teal-800">
            Rekap minggu ini
        </a>
    </div>
    <div class="ui-card p-4 sm:p-5">
        <p class="text-xs text-slate-500">{{ $week['fromLabel'] }}–{{ $week['toLabel'] }}</p>
        <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
            <div class="rounded-xl bg-teal-50 px-3 py-2">
                <p class="text-lg font-semibold text-teal-800">{{ $week['hadir'] }}</p>
                <p class="text-xs text-teal-700">Hadir</p>
            </div>
            <div class="rounded-xl bg-amber-50 px-3 py-2">
                <p class="text-lg font-semibold text-amber-800">{{ $week['izin'] }}</p>
                <p class="text-xs text-amber-700">Izin</p>
            </div>
            <div class="rounded-xl bg-sky-50 px-3 py-2">
                <p class="text-lg font-semibold text-sky-800">{{ $week['sakit'] }}</p>
                <p class="text-xs text-sky-700">Sakit</p>
            </div>
            <div class="rounded-xl bg-rose-50 px-3 py-2">
                <p class="text-lg font-semibold text-rose-800">{{ $week['alfa'] }}</p>
                <p class="text-xs text-rose-700">Alfa</p>
            </div>
        </div>
        @if ($week['total'] === 0)
            <p class="mt-3 text-sm text-slate-500">Belum ada absensi dari Senin sampai hari ini.</p>
        @endif
    </div>
</section>
