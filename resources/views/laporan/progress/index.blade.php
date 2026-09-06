<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Progress juz</h1>
        <p class="text-sm text-slate-500">{{ $year?->name ?? 'Belum ada tahun ajaran aktif' }} · hanya ayat lancar</p>
    </x-slot>

    <div class="max-w-lg space-y-3">
        @unless ($year)
            <div class="bg-white rounded-2xl border border-slate-200 p-5 text-sm text-slate-600">
                Aktifkan tahun ajaran dulu agar progress bisa dihitung.
            </div>
        @else
            @forelse ($rows as $row)
                <a href="{{ route('laporan.progress.show', $row['santri']) }}"
                   class="block bg-white rounded-2xl border border-slate-200 p-4">
                    <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-slate-800">{{ $row['santri']->user->name }}</p>
                        <p class="text-sm text-slate-500">NIS {{ $row['santri']->nis }} · {{ $row['halaqah']->name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-teal-800">{{ $row['progress']->totalPercent }}%</p>
                        @if ($lead = $row['progress']->leadingJuz())
                            <p class="text-xs text-slate-500">Juz {{ $lead->number }} · {{ $lead->percent }}%</p>
                        @endif
                    </div>
                    </div>
                    <div class="mt-3 h-2.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-teal-600" style="width: {{ min($row['progress']->totalPercent, 100) }}%"></div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">{{ $row['progress']->uniqueAyahCount }} ayat unik lancar</p>
                </a>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200 p-5 text-sm text-slate-600">
                    Belum ada santri di halaqah aktif.
                </div>
            @endforelse
        @endunless
    </div>
</x-app-layout>
