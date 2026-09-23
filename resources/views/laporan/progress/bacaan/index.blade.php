<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Laporan</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Progress</h1>
            <p class="text-sm text-slate-500">
                Bacaan: Alquran (juz aktif) & Iqro
            </p>
        </div>
    </x-slot>

    <div class="max-w-2xl space-y-3">
        <div class="flex gap-2">
            <a href="{{ route('laporan.progress.bacaan.index') }}" class="btn-primary min-h-10 px-4 text-sm">Bacaan</a>
            <a href="{{ route('laporan.progress.hafalan.index') }}" class="btn-secondary min-h-10 px-4 text-sm">Hafalan</a>
        </div>

        @forelse ($rows as $row)
            @php $data = $row['data']; @endphp
            <a href="{{ route('laporan.progress.bacaan.show', $row['santri']) }}"
               class="ui-card block p-4 transition hover:-translate-y-0.5 hover:shadow-lift">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-teal-950">{{ $row['santri']->user->name }}</p>
                        <p class="text-sm text-slate-500">NIS {{ $row['santri']->nis }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $data['summary'] }}</p>
                    </div>
                    <div class="text-right">
                        @if ($data['alquran'])
                            <p class="text-xl font-semibold tabular-nums text-teal-800">
                                {{ $data['alquran']->percentLabel() }}
                            </p>
                            <p class="text-xs text-slate-500">Juz {{ $data['alquran']->juz->number }} dikerjakan</p>
                        @elseif ($data['iqroLabel'])
                            <p class="text-sm font-semibold text-teal-800">{{ $data['iqroLabel'] }}</p>
                        @else
                            <p class="text-sm text-slate-400">—</p>
                        @endif
                    </div>
                </div>
                @if ($data['alquran'])
                    <x-progress class="mt-3" :value="$data['alquran']->juz->percent" />
                @endif
            </a>
        @empty
            <x-empty>Belum ada santri aktif.</x-empty>
        @endforelse
    </div>
</x-app-layout>
