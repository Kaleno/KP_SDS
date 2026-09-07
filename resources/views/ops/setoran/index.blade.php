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
        @php
            $statusFilter = $filters['status'] ?? '';
            $hasFilters = filled($filters['santri_id'] ?? null)
                || filled($filters['quran_surah_id'] ?? null)
                || filled($statusFilter)
                || filled($filters['date_from'] ?? null)
                || filled($filters['date_to'] ?? null);
            $advancedOpen = filled($filters['santri_id'] ?? null)
                || filled($filters['quran_surah_id'] ?? null)
                || $activePeriod === 'custom';
            $baseQuery = array_filter([
                'santri_id' => $filters['santri_id'] ?? null,
                'quran_surah_id' => $filters['quran_surah_id'] ?? null,
                'status' => $statusFilter ?: null,
                'date_from' => $filters['date_from'] ?? null,
                'date_to' => $filters['date_to'] ?? null,
            ], fn ($value) => $value !== null && $value !== '');
            $filterUrl = function (array $overrides) use ($baseQuery): string {
                return route('ops.setoran.index', array_filter(
                    array_merge($baseQuery, $overrides),
                    fn ($value) => $value !== null && $value !== '',
                ));
            };
            $periodUrl = function (string $period) use ($filterUrl, $activePeriod, $today, $weekFrom, $monthFrom): string {
                $clear = ['date_from' => '', 'date_to' => ''];

                return match ($period) {
                    'today' => $filterUrl($activePeriod === 'today' ? $clear : ['date_from' => $today, 'date_to' => $today]),
                    'week' => $filterUrl($activePeriod === 'week' ? $clear : ['date_from' => $weekFrom, 'date_to' => $today]),
                    'month' => $filterUrl($activePeriod === 'month' ? $clear : ['date_from' => $monthFrom, 'date_to' => $today]),
                    default => $filterUrl($clear),
                };
            };
        @endphp

        <div x-data="{ open: {{ $advancedOpen ? 'true' : 'false' }} }" class="space-y-2">
            <div class="ui-segment" role="group" aria-label="Rentang waktu">
                <a href="{{ $periodUrl('all') }}" class="{{ $activePeriod === 'all' ? 'is-active' : '' }}" @if ($activePeriod === 'all') aria-current="true" @endif>Semua</a>
                <a href="{{ $periodUrl('today') }}" class="{{ $activePeriod === 'today' ? 'is-active' : '' }}" @if ($activePeriod === 'today') aria-current="true" @endif>Hari ini</a>
                <a href="{{ $periodUrl('week') }}" class="{{ $activePeriod === 'week' ? 'is-active' : '' }}" aria-label="Minggu ini" @if ($activePeriod === 'week') aria-current="true" @endif>
                    <span class="sm:hidden">Minggu</span>
                    <span class="hidden sm:inline">Minggu ini</span>
                </a>
                <a href="{{ $periodUrl('month') }}" class="{{ $activePeriod === 'month' ? 'is-active' : '' }}" aria-label="Bulan ini" @if ($activePeriod === 'month') aria-current="true" @endif>
                    <span class="sm:hidden">Bulan</span>
                    <span class="hidden sm:inline">Bulan ini</span>
                </a>
            </div>

            <div class="flex items-center gap-2">
                <div class="flex min-w-0 flex-1 flex-wrap items-center gap-1" role="group" aria-label="Status setoran">
                    @foreach ($statuses as $status)
                        <a
                            href="{{ $filterUrl(['status' => $statusFilter === $status->value ? '' : $status->value]) }}"
                            class="ui-filter-chip {{ $statusFilter === $status->value ? 'is-'.$status->value : '' }}"
                            @if ($statusFilter === $status->value) aria-pressed="true" @else aria-pressed="false" @endif
                        >{{ $status->label() }}</a>
                    @endforeach
                </div>
                <button type="button" class="shrink-0 text-[11px] font-semibold text-teal-800" @click="open = ! open">
                    Filter lain
                </button>
                @if ($hasFilters)
                    <a href="{{ route('ops.setoran.index') }}" class="shrink-0 text-[11px] font-semibold text-slate-500">Reset</a>
                @endif
            </div>

            <form method="GET" action="{{ route('ops.setoran.index') }}" class="ui-card mt-2 space-y-3 p-4" x-show="open" x-cloak>
                @if ($statusFilter !== '')
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                @endif
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
                    <x-surah-picker :surahs="$surahs" :selected="$filters['quran_surah_id'] ?? null" :allow-empty="true" />
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <x-input-label for="date_from" value="Dari" />
                        <x-text-input id="date_from" type="date" name="date_from" class="mt-1.5" :value="$filters['date_from'] ?? ''" />
                    </div>
                    <div>
                        <x-input-label for="date_to" value="Sampai" />
                        <x-text-input id="date_to" type="date" name="date_to" class="mt-1.5" :value="$filters['date_to'] ?? ''" />
                    </div>
                </div>
                <button type="submit" class="btn-secondary btn-block">Terapkan</button>
            </form>
        </div>

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
