<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Rekap absensi</h1>
        <p class="text-sm text-slate-500">Persen hadir per santri pada rentang tanggal</p>
    </x-slot>

    <div class="max-w-lg space-y-4">
        <form method="GET" class="bg-white rounded-2xl border border-slate-200 p-4 space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label for="date_from" value="Dari" />
                    <x-text-input id="date_from" type="date" name="date_from" class="mt-1 block w-full text-base rounded-xl" :value="$from" />
                </div>
                <div>
                    <x-input-label for="date_to" value="Sampai" />
                    <x-text-input id="date_to" type="date" name="date_to" class="mt-1 block w-full text-base rounded-xl" :value="$to" />
                </div>
            </div>
            <div>
                <x-input-label for="halaqah_id" value="Halaqah" />
                <select id="halaqah_id" name="halaqah_id" class="mt-1 w-full rounded-xl border-slate-300 text-base min-h-12">
                    <option value="">Semua halaqah</option>
                    @foreach ($halaqahList as $halaqah)
                        <option value="{{ $halaqah->id }}" @selected($selectedHalaqah === $halaqah->id)>{{ $halaqah->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="w-full min-h-12 rounded-xl bg-teal-700 text-white font-semibold">Terapkan</button>
        </form>

        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-sm text-slate-500">Rekap kelompok</p>
            <p class="text-2xl font-semibold text-slate-800 mt-1">{{ $halaqahPercent }}% hadir</p>
            <p class="text-sm text-slate-500 mt-1">
                H {{ $totals['hadir'] }} · I {{ $totals['izin'] }} · S {{ $totals['sakit'] }} · A {{ $totals['alfa'] }}
            </p>
        </div>

        @forelse ($rows as $row)
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-slate-800">{{ $row['santri']?->user->name }}</p>
                        <p class="text-sm text-slate-500">NIS {{ $row['santri']?->nis }}</p>
                    </div>
                    <p class="text-lg font-semibold text-teal-800">{{ $row['percent'] }}%</p>
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
            <div class="bg-white rounded-2xl border border-slate-200 p-5 text-sm text-slate-600">
                Belum ada absensi pada rentang ini.
            </div>
        @endforelse
    </div>
</x-app-layout>
