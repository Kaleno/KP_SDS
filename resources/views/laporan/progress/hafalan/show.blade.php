<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Progress</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">{{ $santri->user->name }}</h1>
            <p class="text-sm text-slate-500">NIS {{ $santri->nis }} · Hafalan</p>
        </div>
    </x-slot>

    <div class="w-full max-w-3xl space-y-4">
        <div class="flex gap-2">
            <a href="{{ route('laporan.progress.bacaan.show', $santri) }}" class="btn-secondary min-h-10 px-4 text-sm">Bacaan</a>
            <a href="{{ route('laporan.progress.hafalan.show', $santri) }}" class="btn-primary min-h-10 px-4 text-sm">Hafalan</a>
        </div>

        @if ($data['juz30'])
            @php $snap = $data['juz30']; @endphp
            <div class="relative overflow-hidden rounded-3xl bg-teal-950 p-6 text-white shadow-lift">
                <p class="text-sm text-teal-100/80">Hafalan Juz 30</p>
                <p class="mt-2 font-display text-4xl font-semibold text-gold-300">{{ $snap->percentLabel() }}</p>
                <p class="mt-2 text-sm text-teal-100/80">{{ $snap->positionLabel }}</p>
                <p class="mt-1 text-xs text-teal-100/60">
                    {{ $snap->juz->lancarCount }}/{{ $snap->juz->ayahTotal }} ayat lancar
                </p>
                <div class="mt-4 h-3 w-full overflow-hidden rounded-full bg-white/10">
                    <div class="h-full rounded-full bg-gradient-to-r from-gold-300 to-gold-500"
                         style="width: {{ min($snap->juz->percent, 100) }}%"></div>
                </div>
            </div>
        @endif

        @if ($data['doaName'])
            <x-card>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-700">Doa terakhir</p>
                        <p class="mt-2 font-display text-2xl font-semibold text-teal-900">{{ $data['doaName'] }}</p>
                    </div>
                    @if ($data['doaStatus'])
                        <x-badge :tone="$data['doaStatus']->value === 'lulus' ? 'ok' : 'warn'">
                            {{ $data['doaStatus']->label() }}
                        </x-badge>
                    @endif
                </div>
            </x-card>
        @endif

        @unless ($data['juz30'] || $data['doaName'])
            <x-empty>Belum ada setoran hafalan untuk santri ini.</x-empty>
        @endunless

        <p class="text-center">
            <a href="{{ route('laporan.progress.hafalan.index') }}" class="ui-link text-sm">Kembali ke daftar progress</a>
        </p>
    </div>
</x-app-layout>
