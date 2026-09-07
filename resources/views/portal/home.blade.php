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

    <div class="max-w-2xl space-y-5">
        @if ($isParent && $children->isNotEmpty())
            <section class="space-y-2">
                <div class="flex items-center justify-between gap-3 px-1">
                    <h2 class="ui-section-title">Pilih anak</h2>
                    <p class="text-xs text-slate-400">{{ $children->count() }} anak tertaut</p>
                </div>
                <div class="flex gap-2 overflow-x-auto pb-1">
                    @foreach ($children as $child)
                        <a href="{{ route('portal.home', ['anak' => $child->id]) }}"
                           class="shrink-0 min-h-11 px-4 rounded-full border text-sm font-semibold flex items-center gap-2 transition
                                {{ $santri?->id === $child->id ? 'bg-teal-800 text-white border-teal-800 shadow-glow' : 'bg-white text-slate-700 border-slate-200 hover:border-teal-400' }}">
                            {{ $child->user->name }}
                            @if ($santri?->id === $child->id)
                                <span class="text-[10px] uppercase tracking-wide {{ $santri?->id === $child->id ? 'text-gold-300' : '' }}">Dipantau</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @unless ($santri)
            <x-empty>Belum ada anak yang ditautkan ke akun ini. Hubungi Ketua agar NIS anak ditautkan, lalu catatan hafalan akan muncul di sini.</x-empty>
        @else
            @php
                $snapshot = $snapshot ?? null;
                $currentJuz = $progress?->currentJuz();
                $todayAttendance = $snapshot['todayAttendance'] ?? null;
                $todaySlots = $snapshot['todaySlots'] ?? collect();
                $todaySetoran = $snapshot['todaySetoran'] ?? collect();
                $needsFollowUp = $snapshot['needsFollowUp'] ?? null;
                $attendanceCounts = $snapshot['attendanceCounts'] ?? [];
                $attendanceTotal = $snapshot['attendanceTotal'] ?? 0;
                $dateLabel = function ($date): string {
                    $value = $date->toDateString();
                    if ($value === now()->toDateString()) {
                        return 'Hari ini';
                    }
                    if ($value === now()->copy()->subDay()->toDateString()) {
                        return 'Kemarin';
                    }

                    return $date->format('d/m/Y');
                };
            @endphp

            <div class="relative overflow-hidden rounded-3xl bg-teal-950 p-6 text-white shadow-lift">
                <div class="pointer-events-none absolute -right-6 top-0 h-32 w-32 rounded-full bg-gold-400/20 blur-2xl"></div>
                <p class="text-[11px] uppercase tracking-[0.2em] text-gold-300">Assalamu'alaikum</p>
                <p class="mt-2 font-display text-2xl font-semibold text-balance">
                    {{ $isParent ? 'Hafalan '.$santri->user->name : $santri->user->name }}
                </p>
                <p class="mt-1 text-sm text-teal-100/80">
                    NIS {{ $santri->nis }}
                    @if ($membership)
                        · {{ $membership->halaqah->name }} · Ustaz {{ $membership->halaqah->ustaz->name }}
                    @endif
                </p>
                <p class="mt-3 text-sm text-teal-100/75">
                    {{ $isParent ? 'Catatan dari pengajar untuk anak Anda.' : 'Catatan hafalan dan kehadiranmu dari pengajar.' }}
                    Anda hanya melihat, tidak mengubah data.
                </p>

                @if ($progress)
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-white/10 px-4 py-3">
                            <p class="font-display text-3xl font-semibold text-gold-300">{{ $progress->uniqueAyahCount }}</p>
                            <p class="mt-1 text-xs text-teal-100/75">ayat lancar dari {{ $progress->quranAyahTotal }}</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-4 py-3">
                            <p class="font-display text-3xl font-semibold text-gold-300">{{ $progress->completedJuzCount() }}/30</p>
                            <p class="mt-1 text-xs text-teal-100/75">juz selesai</p>
                        </div>
                    </div>
                    @if ($currentJuz)
                        <p class="mt-4 text-sm text-teal-100/80">
                            Fokus saat ini: Juz {{ $currentJuz->number }} · {{ $currentJuz->percent }}%
                            <span class="text-teal-100/60">({{ $progress->totalPercent }}% seluruh Al-Qur'an)</span>
                        </p>
                    @endif
                @endif
            </div>

            @if ($snapshot)
                <nav class="flex gap-2 overflow-x-auto pb-1" aria-label="Bagian halaman">
                    <a href="#hari-ini" class="shrink-0 rounded-full bg-white px-3 py-2 text-xs font-semibold text-teal-800 shadow-soft">Hari ini</a>
                    <a href="#hafalan" class="shrink-0 rounded-full bg-white px-3 py-2 text-xs font-semibold text-slate-600 shadow-soft">Hafalan</a>
                    <a href="#setoran" class="shrink-0 rounded-full bg-white px-3 py-2 text-xs font-semibold text-slate-600 shadow-soft">Setoran</a>
                    <a href="#absensi" class="shrink-0 rounded-full bg-white px-3 py-2 text-xs font-semibold text-slate-600 shadow-soft">Absensi</a>
                    <a href="#jadwal" class="shrink-0 rounded-full bg-white px-3 py-2 text-xs font-semibold text-slate-600 shadow-soft">Jadwal</a>
                </nav>

                <section id="hari-ini" class="scroll-mt-24 space-y-3">
                    <div class="flex items-end justify-between gap-3 px-1">
                        <h2 class="ui-section-title">Hari ini</h2>
                        <p class="text-xs text-slate-500">{{ $snapshot['dayLabel'] }}, {{ $snapshot['todayLabel'] }}</p>
                    </div>

                    @if ($needsFollowUp)
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
                            <div class="flex items-start gap-3">
                                <x-icon name="alert" class="mt-0.5 h-5 w-5 shrink-0 text-amber-700" />
                                <div>
                                    <p class="font-semibold text-amber-950">Perlu diulang</p>
                                    <p class="mt-0.5 text-sm text-amber-800">
                                        Setoran terakhir: {{ $needsFollowUp->surah->name_id }} ayat {{ $needsFollowUp->ayahRange() }}.
                                        {{ $needsFollowUp->status->hint() }}.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($todayAttendance?->status === \App\Enums\AttendanceStatus::Alfa)
                        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3">
                            <div class="flex items-start gap-3">
                                <x-icon name="alert" class="mt-0.5 h-5 w-5 shrink-0 text-rose-700" />
                                <div>
                                    <p class="font-semibold text-rose-950">Tidak hadir hari ini</p>
                                    <p class="mt-0.5 text-sm text-rose-800">{{ $todayAttendance->status->hint() }}.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="ui-card p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Kehadiran</p>
                            @if ($todayAttendance)
                                <p class="mt-2 font-display text-xl font-semibold text-teal-950">{{ $todayAttendance->status->label() }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $todayAttendance->status->hint() }}</p>
                            @elseif ($todaySlots->isNotEmpty())
                                <p class="mt-2 font-display text-xl font-semibold text-slate-700">Belum dicatat</p>
                                <p class="mt-1 text-xs text-slate-500">Sesi hari ini belum diisi pengajar</p>
                            @else
                                <p class="mt-2 font-display text-xl font-semibold text-slate-700">Tidak ada sesi</p>
                                <p class="mt-1 text-xs text-slate-500">Tidak ada jadwal halaqah hari ini</p>
                            @endif
                        </div>
                        <div class="ui-card p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Setoran</p>
                            @if ($todaySetoran->isNotEmpty())
                                <p class="mt-2 font-display text-xl font-semibold text-teal-950">{{ $todaySetoran->first()->status->label() }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $todaySetoran->first()->surah->name_id }} ayat {{ $todaySetoran->first()->ayahRange() }}</p>
                            @else
                                <p class="mt-2 font-display text-xl font-semibold text-slate-700">Belum ada</p>
                                <p class="mt-1 text-xs text-slate-500">Belum ada setoran tercatat hari ini</p>
                            @endif
                        </div>
                        <div class="ui-card p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Jadwal</p>
                            @if ($todaySlots->isNotEmpty())
                                <p class="mt-2 font-display text-xl font-semibold text-teal-950">{{ $todaySlots->first()->timeRange() }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $todaySlots->first()->location->name }}</p>
                            @else
                                <p class="mt-2 font-display text-xl font-semibold text-slate-700">Libur halaqah</p>
                                <p class="mt-1 text-xs text-slate-500">Lihat jadwal mingguan di bawah</p>
                            @endif
                        </div>
                    </div>
                </section>
            @endif

            @if ($progress)
                <section id="hafalan" class="scroll-mt-24 space-y-3" x-data="{ showAllJuz: false }">
                    <div class="flex items-end justify-between gap-3 px-1">
                        <h2 class="ui-section-title">Hafalan</h2>
                        <p class="text-xs text-slate-400">warna lebih dalam = lebih lancar</p>
                    </div>

                    <x-card>
                        @if ($currentJuz)
                            <div class="mb-5">
                                <div class="flex items-center justify-between gap-3 text-sm">
                                    <p class="font-semibold text-teal-950">Juz {{ $currentJuz->number }} yang dikerjakan</p>
                                    <p class="text-slate-500">{{ $currentJuz->lancarCount }}/{{ $currentJuz->ayahTotal }} · {{ $currentJuz->percent }}%</p>
                                </div>
                                <x-progress class="mt-2" :value="$currentJuz->percent" />
                            </div>
                        @endif

                        <p class="mb-3 text-sm font-semibold text-slate-700">Peta 30 juz</p>
                        <div class="grid grid-cols-6 gap-1.5 sm:grid-cols-10">
                            @foreach ($progress->juz as $bar)
                                <div class="flex aspect-square flex-col items-center justify-center rounded-lg text-[10px] font-semibold {{ $currentJuz?->number === $bar->number ? 'ring-2 ring-gold-400' : '' }}"
                                     style="background: color-mix(in srgb, #275a50 {{ min($bar->percent, 100) }}%, #e7eee9); color: {{ $bar->percent >= 45 ? '#f7f4ee' : '#275a50' }}"
                                     title="Juz {{ $bar->number }} · {{ $bar->percent }}%">
                                    {{ $bar->number }}
                                </div>
                            @endforeach
                        </div>

                        <button type="button"
                                class="btn-ghost mt-4 min-h-10 w-full text-sm"
                                @click="showAllJuz = !showAllJuz"
                                x-text="showAllJuz ? 'Sembunyikan rincian juz' : 'Lihat rincian 30 juz'">
                            Lihat rincian 30 juz
                        </button>

                        <div class="mt-4 space-y-3" x-show="showAllJuz" x-cloak>
                            @foreach ($progress->juz as $bar)
                                <div>
                                    <div class="mb-1 flex items-center justify-between text-sm">
                                        <span class="font-medium text-slate-700">Juz {{ $bar->number }}</span>
                                        <span class="text-slate-500">{{ $bar->lancarCount }}/{{ $bar->ayahTotal }} · {{ $bar->percent }}%</span>
                                    </div>
                                    <x-progress :value="$bar->percent" />
                                </div>
                            @endforeach
                        </div>
                    </x-card>
                </section>
            @elseif ($year)
                <x-empty>Belum ada hafalan lancar pada tahun ajaran ini.</x-empty>
            @endif

            <section id="setoran" class="scroll-mt-24 space-y-2">
                <h2 class="ui-section-title px-1">Setoran terakhir</h2>
                @forelse ($setoran as $item)
                    <div class="ui-card p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-teal-950">{{ $item->surah->name_id }} ayat {{ $item->ayahRange() }}</p>
                                <p class="text-sm text-slate-500">{{ $dateLabel($item->setoran_date) }} · {{ $item->setoran_date->format('d/m/Y') }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $item->status->hint() }}</p>
                            </div>
                            <x-badge :tone="$item->status->value === 'lancar' ? 'ok' : ($item->status->value === 'ulang' ? 'warn' : 'info')">
                                {{ $item->status->label() }}
                            </x-badge>
                        </div>
                    </div>
                @empty
                    <x-empty>Belum ada setoran. Pengajar akan mencatat setoran setelah pertemuan halaqah.</x-empty>
                @endforelse
            </section>

            <section id="absensi" class="scroll-mt-24 space-y-3">
                <div class="flex items-end justify-between gap-3 px-1">
                    <h2 class="ui-section-title">Absensi terkini</h2>
                    @if ($attendanceTotal > 0)
                        <p class="text-xs text-slate-500">{{ $attendanceCounts['hadir'] ?? 0 }} hadir · {{ $attendanceCounts['alfa'] ?? 0 }} alfa</p>
                    @endif
                </div>

                @if ($attendanceTotal > 0)
                    <div class="grid grid-cols-4 gap-2">
                        <div class="rounded-xl bg-teal-50 px-2 py-2 text-center">
                            <p class="font-semibold text-teal-800">{{ $attendanceCounts['hadir'] ?? 0 }}</p>
                            <p class="text-[11px] text-teal-700">Hadir</p>
                        </div>
                        <div class="rounded-xl bg-amber-50 px-2 py-2 text-center">
                            <p class="font-semibold text-amber-800">{{ $attendanceCounts['izin'] ?? 0 }}</p>
                            <p class="text-[11px] text-amber-700">Izin</p>
                        </div>
                        <div class="rounded-xl bg-sky-50 px-2 py-2 text-center">
                            <p class="font-semibold text-sky-800">{{ $attendanceCounts['sakit'] ?? 0 }}</p>
                            <p class="text-[11px] text-sky-700">Sakit</p>
                        </div>
                        <div class="rounded-xl bg-rose-50 px-2 py-2 text-center">
                            <p class="font-semibold text-rose-800">{{ $attendanceCounts['alfa'] ?? 0 }}</p>
                            <p class="text-[11px] text-rose-700">Alfa</p>
                        </div>
                    </div>
                @endif

                @forelse ($attendances as $row)
                    <div class="ui-card flex items-center justify-between gap-3 p-4">
                        <div>
                            <p class="font-semibold text-teal-950">{{ $dateLabel($row->session->session_date) }} · {{ $row->session->session_date->format('d/m/Y') }}</p>
                            <p class="text-sm text-slate-500">
                                {{ $row->session->schedule->timeRange() }}
                                @if ($row->session->schedule->location)
                                    · {{ $row->session->schedule->location->name }}
                                @endif
                            </p>
                            <p class="mt-1 text-xs text-slate-500">{{ $row->status->hint() }}</p>
                        </div>
                        <x-badge :tone="$row->status->value === 'hadir' ? 'ok' : ($row->status->value === 'alfa' ? 'danger' : ($row->status->value === 'izin' ? 'warn' : 'info'))">
                            {{ $row->status->label() }}
                        </x-badge>
                    </div>
                @empty
                    <x-empty>Belum ada absensi. Kehadiran muncul setelah pengajar membuka sesi.</x-empty>
                @endforelse
            </section>

            <section id="jadwal" class="scroll-mt-24 space-y-2">
                <h2 class="ui-section-title px-1">Jadwal halaqah</h2>
                @forelse ($schedules as $slot)
                    @php $isToday = (int) $slot->day_of_week === now()->isoWeekday(); @endphp
                    <div class="ui-card p-4 {{ $isToday ? 'border-teal-200 bg-teal-50/60' : '' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-teal-950">{{ \App\Support\WeekDay::label($slot->day_of_week) }}</p>
                                <p class="text-sm text-slate-500">{{ $slot->timeRange() }} · {{ $slot->location->name }}</p>
                            </div>
                            @if ($isToday)
                                <x-badge tone="ok">Hari ini</x-badge>
                            @endif
                        </div>
                    </div>
                @empty
                    <x-empty>Belum ada jadwal. Ketua akan mengatur hari dan jam halaqah.</x-empty>
                @endforelse
            </section>
        @endunless
    </div>
</x-app-layout>
