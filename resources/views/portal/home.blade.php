<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">{{ $isParent ? 'Orang tua' : 'Santri' }}</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">
                {{ $isParent ? 'Pantau anak' : 'Beranda' }}
            </h1>
            <p class="mt-0.5 text-sm text-slate-500">
                {{ $year?->name ?? 'Belum ada tahun ajaran aktif' }} · hanya melihat, tidak mengubah data
            </p>
        </div>
    </x-slot>

    <div class="max-w-2xl space-y-4">
        @if ($isParent && $children->isNotEmpty())
            <div class="flex gap-2 overflow-x-auto pb-1">
                @foreach ($children as $child)
                    <a href="{{ route('portal.home', ['anak' => $child->id]) }}"
                       class="shrink-0 min-h-11 px-4 rounded-full border text-sm font-semibold flex items-center transition
                            {{ $santri?->id === $child->id ? 'bg-teal-800 text-white border-teal-800 shadow-glow' : 'bg-white text-slate-700 border-slate-200 hover:border-teal-400' }}">
                        {{ $child->user->name }}
                    </a>
                @endforeach
            </div>
        @endif

        @unless ($santri)
            <x-empty>Belum ada anak yang ditautkan ke akun ini. Hubungi Ketua untuk taut NIS.</x-empty>
        @else
            <div class="relative overflow-hidden rounded-3xl bg-teal-950 p-6 text-white shadow-lift">
                <div class="pointer-events-none absolute -right-6 top-0 h-32 w-32 rounded-full bg-gold-400/20 blur-2xl"></div>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-display text-2xl font-semibold">{{ $santri->user->name }}</p>
                        <p class="mt-1 text-sm text-teal-100/80">NIS {{ $santri->nis }}</p>
                        @if ($membership)
                            <p class="mt-1 text-sm text-teal-100/70">
                                {{ $membership->halaqah->name }} · Ustaz {{ $membership->halaqah->ustaz->name }}
                            </p>
                        @endif
                    </div>
                    @if ($progress)
                        <div class="text-right">
                            <p class="font-display text-4xl font-semibold text-gold-300">{{ $progress->totalPercent }}%</p>
                            <p class="text-xs text-teal-100/70">progress</p>
                        </div>
                    @endif
                </div>
                @if ($progress)
                    <div class="mt-5">
                        <div class="h-3 w-full overflow-hidden rounded-full bg-white/10">
                            <div class="h-full rounded-full bg-gradient-to-r from-gold-300 to-gold-500" style="width: {{ min($progress->totalPercent, 100) }}%"></div>
                        </div>
                        <p class="mt-2 text-xs text-teal-100/70">
                            {{ $progress->uniqueAyahCount }} / {{ $progress->quranAyahTotal }} ayat unik lancar
                            @if ($lead = $progress->leadingJuz())
                                · Juz {{ $lead->number }} {{ $lead->percent }}%
                            @endif
                        </p>
                    </div>
                @endif
            </div>

            @if ($progress)
                <x-card>
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-sm font-semibold text-slate-700">30 juz</p>
                        <p class="text-xs text-slate-400">warna lebih dalam = lebih lancar</p>
                    </div>
                    <div class="grid grid-cols-6 gap-1.5 sm:grid-cols-10">
                        @foreach ($progress->juz as $bar)
                            <div class="aspect-square rounded-lg flex flex-col items-center justify-center text-[10px] font-semibold"
                                 style="background: color-mix(in srgb, #275a50 {{ min($bar->percent, 100) }}%, #e7eee9); color: {{ $bar->percent >= 45 ? '#f7f4ee' : '#275a50' }}"
                                 title="Juz {{ $bar->number }} · {{ $bar->percent }}%">
                                {{ $bar->number }}
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-5 space-y-3">
                        @foreach ($progress->juz as $bar)
                            <div>
                                <div class="flex items-center justify-between text-sm mb-1">
                                    <span class="font-medium text-slate-700">Juz {{ $bar->number }}</span>
                                    <span class="text-slate-500">{{ $bar->lancarCount }}/{{ $bar->ayahTotal }} · {{ $bar->percent }}%</span>
                                </div>
                                <x-progress :value="$bar->percent" />
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif

            <section class="space-y-2">
                <h2 class="ui-section-title px-1">Setoran terakhir</h2>
                @forelse ($setoran as $item)
                    <div class="ui-card p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-teal-950">{{ $item->surah->name_id }} ayat {{ $item->ayahRange() }}</p>
                                <p class="text-sm text-slate-500">{{ $item->setoran_date->format('d/m/Y') }}</p>
                            </div>
                            <x-badge :tone="$item->status->value === 'lancar' ? 'ok' : ($item->status->value === 'ulang' ? 'warn' : 'info')">
                                {{ $item->status->label() }}
                            </x-badge>
                        </div>
                    </div>
                @empty
                    <x-empty>Belum ada setoran.</x-empty>
                @endforelse
            </section>

            <section class="space-y-2">
                <h2 class="ui-section-title px-1">Absensi terkini</h2>
                @forelse ($attendances as $row)
                    <div class="ui-card p-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-teal-950">{{ $row->session->session_date->format('d/m/Y') }}</p>
                            <p class="text-sm text-slate-500">
                                {{ $row->session->schedule->timeRange() }}
                                @if ($row->session->schedule->location)
                                    · {{ $row->session->schedule->location->name }}
                                @endif
                            </p>
                        </div>
                        <span class="text-sm font-semibold {{ $row->status->value === 'alfa' ? 'text-rose-700' : 'text-teal-800' }}">
                            {{ $row->status->label() }}
                        </span>
                    </div>
                @empty
                    <x-empty>Belum ada absensi.</x-empty>
                @endforelse
            </section>

            <section class="space-y-2">
                <h2 class="ui-section-title px-1">Jadwal halaqah</h2>
                @forelse ($schedules as $slot)
                    <div class="ui-card p-4">
                        <p class="font-semibold text-teal-950">{{ \App\Support\WeekDay::label($slot->day_of_week) }}</p>
                        <p class="text-sm text-slate-500">{{ $slot->timeRange() }} · {{ $slot->location->name }}</p>
                    </div>
                @empty
                    <x-empty>Belum ada jadwal.</x-empty>
                @endforelse
            </section>
        @endunless
    </div>
</x-app-layout>
