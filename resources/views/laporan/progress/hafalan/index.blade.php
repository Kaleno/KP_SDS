<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Laporan</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Progress</h1>
        </div>
    </x-slot>

    <div class="ui-page !space-y-3">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('laporan.progress.bacaan.index') }}" class="btn-secondary min-h-10 px-4 text-sm">Bacaan</a>
            <a href="{{ route('laporan.progress.hafalan.index') }}" class="btn-primary min-h-10 px-4 text-sm">Hafalan</a>
        </div>

        <div class="grid gap-3 lg:grid-cols-2">
        @forelse ($rows as $row)
            @php $data = $row['data']; @endphp
            <a href="{{ route('laporan.progress.hafalan.show', $row['santri']) }}"
               class="ui-card block p-4 transition hover:-translate-y-0.5 hover:shadow-lift">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-teal-950">{{ $row['santri']->user->name }}</p>
                        <p class="text-sm text-slate-500">NIS {{ $row['santri']->nis }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $data['summary'] }}</p>
                        @if ($data['doaName'])
                            <p class="mt-1 text-xs text-slate-500">
                                Doa terakhir: {{ $data['doaName'] }}
                                @if ($data['doaStatus'])
                                    · {{ $data['doaStatus']->label() }}
                                @endif
                            </p>
                        @endif
                    </div>
                    <div class="shrink-0 text-right">
                        @if ($data['juz30'])
                            <p class="text-xl font-semibold tabular-nums text-teal-800">
                                {{ $data['juz30']->percentLabel() }}
                            </p>
                            <p class="text-xs text-slate-500">Juz 30</p>
                        @elseif ($data['doaName'])
                            <x-badge :tone="$data['doaStatus']?->value === 'lulus' ? 'ok' : 'warn'">
                                {{ $data['doaStatus']?->label() ?? 'Doa' }}
                            </x-badge>
                        @else
                            <p class="text-sm text-slate-400">—</p>
                        @endif
                    </div>
                </div>
                @if ($data['juz30'])
                    <x-progress class="mt-3" :value="$data['juz30']->juz->percent" />
                @endif
            </a>
        @empty
            <div class="lg:col-span-2">
                <x-empty>Belum ada santri aktif.</x-empty>
            </div>
        @endforelse
        </div>
    </div>
</x-app-layout>
