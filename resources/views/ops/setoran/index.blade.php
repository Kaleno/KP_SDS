<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="ui-section-title">Operasional</p>
                <h1 class="font-display text-2xl font-semibold text-teal-950">Setoran</h1>
                <p class="text-sm text-slate-500">Riwayat hafalan halaqah Anda</p>
            </div>
            <a href="{{ route('ops.setoran.create') }}" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" /> Input
            </a>
        </div>
    </x-slot>

    <div class="max-w-2xl space-y-3">
        <form method="GET" class="ui-card p-5 space-y-3">
            <div>
                <x-input-label for="santri_id" value="Santri" />
                <select id="santri_id" name="santri_id" class="mt-1.5 ui-input">
                    <option value="">Semua</option>
                    @foreach ($santriOptions as $option)
                        <option value="{{ $option->id }}" @selected(($filters['santri_id'] ?? '') == $option->id)>
                            {{ $option->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="quran_surah_id" value="Surat" />
                <select id="quran_surah_id" name="quran_surah_id" class="mt-1.5 ui-input">
                    <option value="">Semua</option>
                    @foreach ($surahs as $surah)
                        <option value="{{ $surah->id }}" @selected(($filters['quran_surah_id'] ?? '') == $surah->id)>
                            {{ $surah->id }}. {{ $surah->name_id }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="status" value="Status" />
                <select id="status" name="status" class="mt-1.5 ui-input">
                    <option value="">Semua</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label for="date_from" value="Dari" />
                    <x-text-input id="date_from" type="date" name="date_from" class="mt-1.5" :value="$filters['date_from'] ?? ''" />
                </div>
                <div>
                    <x-input-label for="date_to" value="Sampai" />
                    <x-text-input id="date_to" type="date" name="date_to" class="mt-1.5" :value="$filters['date_to'] ?? ''" />
                </div>
            </div>
            <button type="submit" class="btn-primary btn-block">Filter</button>
        </form>

        @forelse ($setoran as $item)
            <a href="{{ route('ops.setoran.edit', $item) }}" class="ui-card block p-4 transition hover:-translate-y-0.5 hover:shadow-lift">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-teal-950">{{ $item->santri->user->name }}</p>
                        <p class="text-sm text-slate-600">
                            {{ $item->surah->name_id }} ayat {{ $item->ayahRange() }}
                        </p>
                        <p class="text-sm text-slate-500">
                            {{ $item->setoran_date->format('d/m/Y') }} · {{ $item->halaqah->name }}
                        </p>
                    </div>
                    <x-badge :tone="$item->status->value === 'lancar' ? 'ok' : ($item->status->value === 'ulang' ? 'warn' : 'info')">
                        {{ $item->status->label() }}
                    </x-badge>
                </div>
                @if ($item->correction_note)
                    <p class="mt-2 text-xs text-slate-500">Koreksi: {{ $item->correction_note }}</p>
                @endif
            </a>
        @empty
            <x-empty>
                Tidak ada setoran sesuai filter. Ketuk Input untuk mencatat hafalan.
            </x-empty>
        @endforelse

        @if ($setoran->hasPages())
            <div class="pt-2">{{ $setoran->links() }}</div>
        @endif
    </div>
</x-app-layout>
