<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Progress juz</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">{{ $santri->user->name }}</h1>
            <p class="text-sm text-slate-500">NIS {{ $santri->nis }} · {{ $year->name }}</p>
        </div>
    </x-slot>

    @php $current = $progress->currentJuz(); @endphp

    <div class="max-w-2xl space-y-4" x-data="{ showAllJuz: false }">
        <div class="relative overflow-hidden rounded-3xl bg-teal-950 p-6 text-white shadow-lift">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <p class="font-display text-4xl font-semibold text-gold-300">{{ $progress->uniqueAyahCount }}</p>
                    <p class="mt-1 text-sm text-teal-100/80">ayat lancar</p>
                </div>
                <div class="text-right">
                    <p class="font-display text-4xl font-semibold text-gold-300">{{ $progress->completedJuzCount() }}/30</p>
                    <p class="mt-1 text-sm text-teal-100/80">juz selesai</p>
                </div>
            </div>
            <p class="mt-4 text-sm text-teal-100/80">
                {{ $progress->uniqueAyahCount }} / {{ $progress->quranAyahTotal }} ayat unik berstatus lancar
            </p>
            @if ($current)
                <p class="mt-2 text-sm text-teal-100/80">
                    Fokus Juz {{ $current->number }} · {{ $current->percent }}%
                    <span class="text-teal-100/60">({{ $progress->totalPercent }}% seluruh Al-Qur'an)</span>
                </p>
                <div class="mt-4 h-3 w-full overflow-hidden rounded-full bg-white/10">
                    <div class="h-full rounded-full bg-gradient-to-r from-gold-300 to-gold-500" style="width: {{ min($current->percent, 100) }}%"></div>
                </div>
            @endif
        </div>

        @if ($current)
            <x-card>
                <div class="mb-1 flex items-center justify-between text-sm">
                    <span class="font-medium text-slate-700">Juz {{ $current->number }} yang dikerjakan</span>
                    <span class="text-slate-500">{{ $current->lancarCount }}/{{ $current->ayahTotal }} · {{ $current->percent }}%</span>
                </div>
                <x-progress :value="$current->percent" />
            </x-card>
        @endif

        <x-card>
            <p class="mb-3 text-sm font-semibold text-slate-700">Peta 30 juz</p>
            <div class="grid grid-cols-6 gap-1.5 sm:grid-cols-10">
                @foreach ($progress->juz as $bar)
                    <div class="flex aspect-square flex-col items-center justify-center rounded-lg text-[10px] font-semibold {{ $current?->number === $bar->number ? 'ring-2 ring-gold-400' : '' }}"
                         style="background: color-mix(in srgb, #275a50 {{ min($bar->percent, 100) }}%, #e7eee9); color: {{ $bar->percent >= 45 ? '#f7f4ee' : '#275a50' }}"
                         title="Juz {{ $bar->number }} · {{ $bar->percent }}%">
                        {{ $bar->number }}
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn-ghost mt-4 min-h-10 w-full text-sm" @click="showAllJuz = !showAllJuz"
                    x-text="showAllJuz ? 'Sembunyikan rincian juz' : 'Lihat rincian 30 juz'">
                Lihat rincian 30 juz
            </button>
            <div class="mt-4 space-y-3" x-show="showAllJuz" x-cloak>
                @foreach ($progress->juz as $bar)
                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="font-medium text-slate-700">Juz {{ $bar->number }}</span>
                            <span class="text-slate-500">{{ $bar->lancarCount }}/{{ $bar->ayahTotal }} · {{ $bar->percent }}%</span>
                        </div>
                        <x-progress :value="$bar->percent" />
                    </div>
                @endforeach
            </div>
        </x-card>

        <p class="text-center">
            <a href="{{ route('laporan.progress.index') }}" class="ui-link text-sm">Kembali ke daftar</a>
        </p>
    </div>
</x-app-layout>
