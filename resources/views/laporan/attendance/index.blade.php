<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Laporan</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Rekap absensi</h1>
        </div>
    </x-slot>

    <div class="ui-page !space-y-4">
        <form method="GET" class="ui-card space-y-3 p-5">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('laporan.attendance.index', ['date_from' => now()->toDateString(), 'date_to' => now()->toDateString()]) }}" class="btn-secondary min-h-10 text-xs">Hari ini</a>
                <a href="{{ route('laporan.attendance.index', ['date_from' => now()->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString(), 'date_to' => now()->toDateString()]) }}" class="btn-secondary min-h-10 text-xs">Minggu ini</a>
                <a href="{{ route('laporan.attendance.index', ['date_from' => now()->copy()->startOfMonth()->toDateString(), 'date_to' => now()->toDateString()]) }}" class="btn-secondary min-h-10 text-xs">Bulan ini</a>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <x-input-label for="date_from" value="Dari" />
                    <x-text-input id="date_from" type="date" name="date_from" class="mt-1.5" :value="$from" />
                </div>
                <div>
                    <x-input-label for="date_to" value="Sampai" />
                    <x-text-input id="date_to" type="date" name="date_to" class="mt-1.5" :value="$to" />
                </div>
            </div>
            <button type="submit" class="btn-primary btn-block">Terapkan</button>
        </form>

        <div class="ui-card p-5">
            <p class="font-display text-3xl font-semibold text-teal-950">{{ $groupPercent }}% hadir</p>
            <p class="mt-1 text-sm text-slate-500">
                H {{ $totals['hadir'] }} · I {{ $totals['izin'] }} · S {{ $totals['sakit'] }} · A {{ $totals['alfa'] }}
            </p>
        </div>

        <div class="grid gap-3 lg:grid-cols-2">
        @forelse ($rows as $row)
            <div class="ui-card p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-teal-950">{{ $row['santri']?->user->name }}</p>
                        <p class="text-sm text-slate-500">NIS {{ $row['santri']?->nis }}</p>
                    </div>
                    <p class="shrink-0 font-display text-xl font-semibold text-teal-800">{{ $row['percent'] }}%</p>
                </div>
                <div class="mt-3 grid grid-cols-4 gap-2 text-center text-sm">
                    <div class="rounded-xl bg-teal-50 py-2">
                        <p class="font-semibold text-teal-800">{{ $row['hadir'] }}</p>
                        <p class="text-xs text-teal-700">Hadir</p>
                    </div>
                    <div class="rounded-xl bg-amber-50 py-2">
                        <p class="font-semibold text-amber-800">{{ $row['izin'] }}</p>
                        <p class="text-xs text-amber-700">Izin</p>
                    </div>
                    <div class="rounded-xl bg-sky-50 py-2">
                        <p class="font-semibold text-sky-800">{{ $row['sakit'] }}</p>
                        <p class="text-xs text-sky-700">Sakit</p>
                    </div>
                    <div class="rounded-xl bg-rose-50 py-2">
                        <p class="font-semibold text-rose-800">{{ $row['alfa'] }}</p>
                        <p class="text-xs text-rose-700">Alfa</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="lg:col-span-2">
                <x-empty>Belum ada absensi pada rentang ini.</x-empty>
            </div>
        @endforelse
        </div>
    </div>
</x-app-layout>
