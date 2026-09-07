<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Laporan</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Progress juz</h1>
            <p class="text-sm text-slate-500">{{ $year?->name ?? 'Belum ada tahun ajaran aktif' }} · hanya ayat lancar</p>
        </div>
    </x-slot>

    <div class="max-w-2xl space-y-3">
        @unless ($year)
            <x-empty>Aktifkan tahun ajaran dulu agar progress bisa dihitung.</x-empty>
        @else
            @forelse ($rows as $row)
                @php $current = $row['progress']->currentJuz(); @endphp
                <a href="{{ route('laporan.progress.show', $row['santri']) }}"
                   class="ui-card block p-4 transition hover:-translate-y-0.5 hover:shadow-lift">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-teal-950">{{ $row['santri']->user->name }}</p>
                            <p class="text-sm text-slate-500">NIS {{ $row['santri']->nis }} · {{ $row['halaqah']->name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-display text-xl font-semibold text-teal-800">
                                {{ $current ? $current->percent.'%' : $row['progress']->totalPercent.'%' }}
                            </p>
                            @if ($current)
                                <p class="text-xs text-slate-500">Juz {{ $current->number }}</p>
                            @endif
                        </div>
                    </div>
                    <x-progress class="mt-3" :value="$current?->percent ?? $row['progress']->totalPercent" />
                    <p class="mt-2 text-xs text-slate-500">
                        {{ $row['progress']->uniqueAyahCount }} ayat lancar
                        · {{ $row['progress']->totalPercent }}% seluruh Al-Qur'an
                    </p>
                </a>
            @empty
                <x-empty>Belum ada santri di halaqah aktif.</x-empty>
            @endforelse
        @endunless
    </div>
</x-app-layout>
