<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="ui-section-title">Operasional</p>
                <h1 class="font-display text-2xl font-semibold text-teal-950">Setoran</h1>
            </div>
            <a href="{{ route('ops.setoran.create') }}" class="btn-primary shrink-0">
                <x-icon name="plus" class="h-4 w-4" /> Input
            </a>
        </div>
    </x-slot>

    <div class="ui-page !space-y-3">
        @php
            $summaryLine = function (array $row) use ($summary): string {
                if ($summary['active'] === 0) {
                    return 'belum ada santri';
                }

                $parts = [$row['belum'] === 0 ? 'semua sudah' : $row['belum'].' belum'];
                if ($row['ulang'] > 0) {
                    $parts[] = $row['ulang'].' perlu diulang';
                }

                return implode(' · ', $parts);
            };
            $summaryTone = function (array $row) use ($summary): string {
                if ($summary['active'] === 0) {
                    return 'text-slate-400';
                }

                return ($row['belum'] > 0 || $row['ulang'] > 0) ? 'text-amber-700' : 'text-slate-400';
            };
        @endphp

        <form method="GET" action="{{ route('ops.setoran.index') }}" class="grid grid-cols-1 items-end gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]">
            <div class="min-w-0">
                <x-input-label for="date_from" value="Dari" />
                <input id="date_from" type="date" name="date_from" value="{{ $filters['date_from'] }}" required class="ui-input mt-1">
            </div>
            <div class="min-w-0">
                <x-input-label for="date_to" value="Sampai" />
                <input id="date_to" type="date" name="date_to" value="{{ $filters['date_to'] }}" required class="ui-input mt-1">
            </div>
            <button type="submit" class="btn-secondary w-full sm:w-auto">Lihat</button>
        </form>

        <div class="flex items-center justify-between gap-2 text-xs text-slate-500">
            <p>
                @if ($singleDay)
                    {{ $isToday ? 'Hari ini' : 'Satu hari' }} · {{ \App\Support\DateLabel::long($filters['date_from']) }}
                @else
                    {{ \App\Support\DateLabel::dayMonthYear($filters['date_from']) }} – {{ \App\Support\DateLabel::dayMonthYear($filters['date_to']) }}
                @endif
            </p>
            @unless ($isToday)
                <a href="{{ route('ops.setoran.index') }}" class="shrink-0 font-semibold text-teal-800">Hari ini</a>
            @endunless
        </div>

        <div class="grid grid-cols-1 gap-2 sm:grid-cols-3 sm:gap-3">
            <div class="stat-card flex items-center gap-3 !p-3 sm:block lg:!p-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-teal-50 text-teal-800">
                    <x-icon name="users" class="h-5 w-5" />
                </span>
                <div class="min-w-0 sm:mt-4">
                    <p class="font-display text-2xl font-semibold text-teal-950 lg:text-3xl">{{ $summary['active'] }}</p>
                    <p class="text-sm text-slate-500">Santri aktif</p>
                </div>
            </div>
            <div class="stat-card flex items-center gap-3 !p-3 sm:block lg:!p-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-sky-50 text-sky-700">
                    <x-icon name="academic" class="h-5 w-5" />
                </span>
                <div class="min-w-0 sm:mt-4">
                    <p class="font-display text-2xl font-semibold text-teal-950 lg:text-3xl">{{ $summary['hafalan']['sudah'] }}</p>
                    <p class="text-sm text-slate-500">Hafalan</p>
                    <p class="mt-0.5 text-xs {{ $summaryTone($summary['hafalan']) }}">{{ $summaryLine($summary['hafalan']) }}</p>
                </div>
            </div>
            <div class="stat-card flex items-center gap-3 !p-3 sm:block lg:!p-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-teal-50 text-teal-800">
                    <x-icon name="book" class="h-5 w-5" />
                </span>
                <div class="min-w-0 sm:mt-4">
                    <p class="font-display text-2xl font-semibold text-teal-950 lg:text-3xl">{{ $summary['bacaan']['sudah'] }}</p>
                    <p class="text-sm text-slate-500">Bacaan</p>
                    <p class="mt-0.5 text-xs {{ $summaryTone($summary['bacaan']) }}">{{ $summaryLine($summary['bacaan']) }}</p>
                </div>
            </div>
        </div>

        <div class="grid gap-2 lg:grid-cols-2">
            @forelse ($setoran as $item)
                <a href="{{ route('ops.setoran.edit', $item) }}" class="ui-card flex items-center gap-3 px-4 py-3">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-teal-950">{{ $item->santri->user->name }}</p>
                        <p class="truncate text-xs text-slate-500">
                            {{ $item->category?->label() }} · {{ $item->listPassage() }}
                            @if ($item->correction_note)
                                <span class="text-amber-700">· catatan</span>
                            @endif
                        </p>
                    </div>
                    <div class="flex shrink-0 flex-col items-end gap-0.5">
                        <x-badge :tone="$item->status->value === 'lulus' ? 'ok' : 'warn'" class="!px-2 !py-0 !text-[10px] !normal-case !tracking-normal">
                            {{ $item->status->label() }}
                        </x-badge>
                        @unless ($singleDay)
                            <span class="text-[10px] text-slate-400">{{ $item->setoran_date->locale(app()->getLocale())->translatedFormat('j M') }}</span>
                        @endunless
                    </div>
                </a>
            @empty
                <div class="lg:col-span-2">
                    <x-empty>
                        Tidak ada setoran pada rentang ini. Ketuk Input untuk mencatat.
                    </x-empty>
                </div>
            @endforelse
        </div>

        @if ($setoran->hasPages())
            <div>{{ $setoran->links() }}</div>
        @endif
    </div>
</x-app-layout>
