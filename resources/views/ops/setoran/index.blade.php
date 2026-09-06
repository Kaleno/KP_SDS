<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold text-slate-800">Setoran</h1>
                <p class="text-sm text-slate-500">Riwayat hafalan halaqah Anda</p>
            </div>
            <a href="{{ route('ops.setoran.create') }}"
               class="inline-flex items-center justify-center min-h-10 px-4 rounded-xl bg-teal-700 text-white text-sm font-semibold">
                Input
            </a>
        </div>
    </x-slot>

    <div class="max-w-lg space-y-3">
        <form method="GET" class="bg-white rounded-2xl border border-slate-200 p-4 space-y-3">
            <div>
                <x-input-label for="santri_id" value="Santri" />
                <select id="santri_id" name="santri_id" class="mt-1 w-full rounded-xl border-slate-300 text-base min-h-12">
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
                <select id="quran_surah_id" name="quran_surah_id" class="mt-1 w-full rounded-xl border-slate-300 text-base min-h-12">
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
                <select id="status" name="status" class="mt-1 w-full rounded-xl border-slate-300 text-base min-h-12">
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
                    <x-text-input id="date_from" type="date" name="date_from" class="mt-1 block w-full text-base rounded-xl" :value="$filters['date_from'] ?? ''" />
                </div>
                <div>
                    <x-input-label for="date_to" value="Sampai" />
                    <x-text-input id="date_to" type="date" name="date_to" class="mt-1 block w-full text-base rounded-xl" :value="$filters['date_to'] ?? ''" />
                </div>
            </div>
            <button type="submit" class="w-full min-h-12 rounded-xl bg-teal-700 text-white font-semibold">Filter</button>
        </form>

        @forelse ($setoran as $item)
            <a href="{{ route('ops.setoran.edit', $item) }}"
               class="block bg-white rounded-2xl border border-slate-200 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-slate-800">{{ $item->santri->user->name }}</p>
                        <p class="text-sm text-slate-600">
                            {{ $item->surah->name_id }} ayat {{ $item->ayahRange() }}
                        </p>
                        <p class="text-sm text-slate-500">
                            {{ $item->setoran_date->format('d/m/Y') }} · {{ $item->halaqah->name }}
                        </p>
                    </div>
                    <span class="shrink-0 text-xs font-semibold uppercase tracking-wide rounded-full px-2.5 py-1
                        {{ $item->status->value === 'lancar' ? 'bg-teal-50 text-teal-800' : ($item->status->value === 'ulang' ? 'bg-amber-50 text-amber-800' : 'bg-sky-50 text-sky-800') }}">
                        {{ $item->status->label() }}
                    </span>
                </div>
                @if ($item->correction_note)
                    <p class="mt-2 text-xs text-slate-500">Koreksi: {{ $item->correction_note }}</p>
                @endif
            </a>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-5 text-sm text-slate-600">
                Tidak ada setoran sesuai filter. Ketuk Input untuk mencatat hafalan.
            </div>
        @endforelse

        @if ($setoran->hasPages())
            <div class="pt-2">{{ $setoran->links() }}</div>
        @endif
    </div>
</x-app-layout>
