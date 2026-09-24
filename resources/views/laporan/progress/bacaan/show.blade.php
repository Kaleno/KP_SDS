<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Progress</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">{{ $santri->user->name }}</h1>
            <p class="text-sm text-slate-500">NIS {{ $santri->nis }} · Bacaan</p>
        </div>
    </x-slot>

    <div class="w-full max-w-3xl space-y-4">
        <div class="flex gap-2">
            <a href="{{ route('laporan.progress.bacaan.show', $santri) }}" class="btn-primary min-h-10 px-4 text-sm">Bacaan</a>
            <a href="{{ route('laporan.progress.hafalan.show', $santri) }}" class="btn-secondary min-h-10 px-4 text-sm">Hafalan</a>
        </div>

        @if ($data['alquran'])
            @php $snap = $data['alquran']; @endphp
            <div class="relative overflow-hidden rounded-3xl bg-teal-950 p-6 text-white shadow-lift">
                <p class="text-sm text-teal-100/80">Bacaan Alquran · Juz aktif</p>
                <p class="mt-2 font-display text-4xl font-semibold text-gold-300">{{ $snap->percentLabel() }}</p>
                <p class="mt-2 text-sm text-teal-100/80">{{ $snap->positionLabel }}</p>
                <p class="mt-1 text-xs text-teal-100/60">
                    {{ $snap->juz->lancarCount }}/{{ $snap->juz->ayahTotal }} ayat lancar di Juz {{ $snap->juz->number }}
                </p>
                <div class="mt-4 h-3 w-full overflow-hidden rounded-full bg-white/10">
                    <div class="h-full rounded-full bg-gradient-to-r from-gold-300 to-gold-500"
                         style="width: {{ min($snap->juz->percent, 100) }}%"></div>
                </div>
            </div>
        @endif

        @if ($data['iqroLabel'])
            <x-card>
                <p class="text-sm font-semibold text-slate-700">Bacaan Iqro</p>
                <p class="mt-2 font-display text-2xl font-semibold text-teal-900">{{ $data['iqroLabel'] }}</p>
            </x-card>
        @endif

        @unless ($data['alquran'] || $data['iqroLabel'])
            <x-empty>Belum ada setoran bacaan untuk santri ini.</x-empty>
        @endunless

        <p class="text-center">
            <a href="{{ route('laporan.progress.bacaan.index') }}" class="ui-link text-sm">Kembali ke daftar progress</a>
        </p>
    </div>
</x-app-layout>
