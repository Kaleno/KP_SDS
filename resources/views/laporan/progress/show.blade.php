<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-slate-800">{{ $santri->user->name }}</h1>
            <p class="text-sm text-slate-500">NIS {{ $santri->nis }} · {{ $year->name }}</p>
        </div>
    </x-slot>

    <div class="max-w-lg space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-3xl font-semibold text-teal-800">{{ $progress->totalPercent }}%</p>
            <p class="text-sm text-slate-500 mt-1">
                {{ $progress->uniqueAyahCount }} / {{ $progress->quranAyahTotal }} ayat unik berstatus lancar
            </p>
            <div class="mt-3 h-3 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-teal-600" style="width: {{ min($progress->totalPercent, 100) }}%"></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-3">
            @foreach ($progress->juz as $bar)
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="font-medium text-slate-700">Juz {{ $bar->number }}</span>
                        <span class="text-slate-500">{{ $bar->lancarCount }}/{{ $bar->ayahTotal }} · {{ $bar->percent }}%</span>
                    </div>
                    <div class="h-2.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-teal-600" style="width: {{ min($bar->percent, 100) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <p class="text-center">
            <a href="{{ route('laporan.progress.index') }}" class="text-sm text-teal-700 font-medium">Kembali ke daftar</a>
        </p>
    </div>
</x-app-layout>
