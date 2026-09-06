<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Progress juz</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">{{ $santri->user->name }}</h1>
            <p class="text-sm text-slate-500">NIS {{ $santri->nis }} · {{ $year->name }}</p>
        </div>
    </x-slot>

    <div class="max-w-2xl space-y-4">
        <div class="relative overflow-hidden rounded-3xl bg-teal-950 p-6 text-white shadow-lift">
            <p class="font-display text-4xl font-semibold text-gold-300">{{ $progress->totalPercent }}%</p>
            <p class="mt-1 text-sm text-teal-100/80">
                {{ $progress->uniqueAyahCount }} / {{ $progress->quranAyahTotal }} ayat unik berstatus lancar
            </p>
            <div class="mt-4 h-3 w-full overflow-hidden rounded-full bg-white/10">
                <div class="h-full rounded-full bg-gradient-to-r from-gold-300 to-gold-500" style="width: {{ min($progress->totalPercent, 100) }}%"></div>
            </div>
        </div>

        <x-card class="space-y-3">
            @foreach ($progress->juz as $bar)
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="font-medium text-slate-700">Juz {{ $bar->number }}</span>
                        <span class="text-slate-500">{{ $bar->lancarCount }}/{{ $bar->ayahTotal }} · {{ $bar->percent }}%</span>
                    </div>
                    <x-progress :value="$bar->percent" />
                </div>
            @endforeach
        </x-card>

        <p class="text-center">
            <a href="{{ route('laporan.progress.index') }}" class="ui-link text-sm">Kembali ke daftar</a>
        </p>
    </div>
</x-app-layout>
