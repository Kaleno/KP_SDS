<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-slate-800">
                {{ $isParent ? 'Pantau anak' : 'Beranda' }}
            </h1>
            <p class="text-sm text-slate-500">
                {{ $year?->name ?? 'Belum ada tahun ajaran aktif' }} · hanya melihat, tidak mengubah data
            </p>
        </div>
    </x-slot>

    <div class="max-w-lg space-y-4">
        @if ($isParent && $children->isNotEmpty())
            <div class="flex gap-2 overflow-x-auto pb-1">
                @foreach ($children as $child)
                    <a href="{{ route('portal.home', ['anak' => $child->id]) }}"
                       class="shrink-0 min-h-11 px-4 rounded-full border text-sm font-medium flex items-center
                            {{ $santri?->id === $child->id ? 'bg-teal-700 text-white border-teal-700' : 'bg-white text-slate-700 border-slate-200' }}">
                        {{ $child->user->name }}
                    </a>
                @endforeach
            </div>
        @endif

        @unless ($santri)
            <div class="bg-white rounded-2xl border border-slate-200 p-5 text-sm text-slate-600">
                Belum ada anak yang ditautkan ke akun ini. Hubungi Ketua untuk taut NIS.
            </div>
        @else
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <p class="font-semibold text-slate-800">{{ $santri->user->name }}</p>
                <p class="text-sm text-slate-500">NIS {{ $santri->nis }}</p>
                @if ($membership)
                    <p class="text-sm text-slate-500 mt-1">
                        {{ $membership->halaqah->name }} · Ustaz {{ $membership->halaqah->ustaz->name }}
                    </p>
                @endif
            </div>

            @if ($progress)
                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <p class="text-sm text-slate-500">Progress hafalan</p>
                    <p class="text-3xl font-semibold text-teal-800 mt-1">{{ $progress->totalPercent }}%</p>
                    <p class="text-sm text-slate-500 mt-1">
                        {{ $progress->uniqueAyahCount }} / {{ $progress->quranAyahTotal }} ayat unik lancar
                        @if ($lead = $progress->leadingJuz())
                            · Juz {{ $lead->number }} {{ $lead->percent }}%
                        @endif
                    </p>
                    <div class="mt-3 h-3 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-teal-600" style="width: {{ min($progress->totalPercent, 100) }}%"></div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-3">
                    <p class="text-sm font-semibold text-slate-700">30 juz</p>
                    @foreach ($progress->juz as $bar)
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="font-medium text-slate-700">Juz {{ $bar->number }}</span>
                                <span class="text-slate-500">{{ $bar->lancarCount }}/{{ $bar->ayahTotal }} · {{ $bar->percent }}%</span>
                            </div>
                            <div class="h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-teal-600" style="width: {{ min($bar->percent, 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <section class="space-y-2">
                <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide">Setoran terakhir</h2>
                @forelse ($setoran as $item)
                    <div class="bg-white rounded-2xl border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-medium text-slate-800">{{ $item->surah->name_id }} ayat {{ $item->ayahRange() }}</p>
                                <p class="text-sm text-slate-500">{{ $item->setoran_date->format('d/m/Y') }}</p>
                            </div>
                            <span class="shrink-0 text-xs font-semibold uppercase tracking-wide rounded-full px-2.5 py-1
                                {{ $item->status->value === 'lancar' ? 'bg-teal-50 text-teal-800' : ($item->status->value === 'ulang' ? 'bg-amber-50 text-amber-800' : 'bg-sky-50 text-sky-800') }}">
                                {{ $item->status->label() }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 text-sm text-slate-500">Belum ada setoran.</div>
                @endforelse
            </section>

            <section class="space-y-2">
                <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide">Absensi terkini</h2>
                @forelse ($attendances as $row)
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="font-medium text-slate-800">{{ $row->session->session_date->format('d/m/Y') }}</p>
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
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 text-sm text-slate-500">Belum ada absensi.</div>
                @endforelse
            </section>

            <section class="space-y-2">
                <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide">Jadwal halaqah</h2>
                @forelse ($schedules as $slot)
                    <div class="bg-white rounded-2xl border border-slate-200 p-4">
                        <p class="font-medium text-slate-800">{{ \App\Support\WeekDay::label($slot->day_of_week) }}</p>
                        <p class="text-sm text-slate-500">{{ $slot->timeRange() }} · {{ $slot->location->name }}</p>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 text-sm text-slate-500">Belum ada jadwal.</div>
                @endforelse
            </section>
        @endunless
    </div>
</x-app-layout>
