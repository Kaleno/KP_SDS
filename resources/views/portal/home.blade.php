<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Santri</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Beranda</h1>
            <p class="mt-0.5 text-sm text-slate-500">
                Hanya melihat, tidak mengubah data
            </p>
        </div>
    </x-slot>

    <div class="max-w-2xl space-y-5">
        @unless ($santri)
            <x-empty>Profil santri belum tersedia. Hubungi Ketua DKM.</x-empty>
        @else
            @php
                $snapshot = $snapshot ?? null;
                $bacaanSnap = $bacaan['alquran'] ?? null;
                $currentJuz = $bacaanSnap?->juz ?? $progress?->currentJuz();
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
                    {{ $santri->user->name }}
                </p>
                <p class="mt-1 text-sm text-teal-100/80">
                    NIS {{ $santri->nis }}
                </p>
                <p class="mt-3 text-sm text-teal-100/75">
                    Catatan bacaan, hafalan, dan kehadiranmu dari pengajar.
                    Anda hanya melihat, tidak mengubah data.
                </p>

                @if ($bacaanSnap)
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-white/10 px-4 py-3">
                            <p class="font-display text-3xl font-semibold text-gold-300">{{ $bacaanSnap->percentLabel() }}</p>
                            <p class="mt-1 text-xs text-teal-100/75">Juz {{ $bacaanSnap->juz->number }} dikerjakan</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-4 py-3">
                            <p class="font-display text-lg font-semibold text-gold-300 leading-snug">{{ $bacaanSnap->surahName }}</p>
                            <p class="mt-1 text-xs text-teal-100/75">ayat {{ $bacaanSnap->ayahStart }}–{{ $bacaanSnap->ayahEnd }}</p>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-teal-100/80">{{ $bacaanSnap->positionLabel }}</p>
                @elseif (($bacaan['iqroLabel'] ?? null))
                    <div class="mt-5 rounded-2xl bg-white/10 px-4 py-3">
                        <p class="font-display text-2xl font-semibold text-gold-300">{{ $bacaan['iqroLabel'] }}</p>
                        <p class="mt-1 text-xs text-teal-100/75">Bacaan Iqro terakhir</p>
                    </div>
                @elseif ($progress && $currentJuz && $currentJuz->lancarCount > 0)
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-white/10 px-4 py-3">
                            <p class="font-display text-3xl font-semibold text-gold-300">{{ $currentJuz->percent }}%</p>
                            <p class="mt-1 text-xs text-teal-100/75">Juz {{ $currentJuz->number }} dikerjakan</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-4 py-3">
                            <p class="font-display text-3xl font-semibold text-gold-300">{{ $currentJuz->lancarCount }}</p>
                            <p class="mt-1 text-xs text-teal-100/75">ayat lancar di juz ini</p>
                        </div>
                    </div>
                @endif

                @if (($hafalan['juz30'] ?? null) || ($hafalan['doaName'] ?? null))
                    <div class="mt-4 rounded-2xl bg-white/5 px-4 py-3 text-sm text-teal-100/85">
                        @if ($hafalan['juz30'] ?? null)
                            <p>Hafalan Juz 30 · {{ $hafalan['juz30']->percentLabel() }}</p>
                        @endif
                        @if ($hafalan['doaName'] ?? null)
                            <p class="mt-1">Doa: {{ $hafalan['doaName'] }}
                                @if ($hafalan['doaStatus'] ?? null)
                                    · {{ $hafalan['doaStatus']->label() }}
                                @endif
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <section class="ui-card p-5 space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="ui-section-title">Info pembayaran</h2>
                    <p class="text-xs text-slate-400">Rp {{ number_format($sppAmount ?? \App\Support\AppSettings::DefaultSppMonthlyAmount, 0, ',', '.') }}/bln</p>
                </div>
                @if (($unpaidMonths ?? 0) > 1)
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        Nunggak {{ $unpaidMonths }} bulan · Rp {{ number_format($unpaidMonths * ($sppAmount ?? 0), 0, ',', '.') }}
                    </div>
                @elseif (($unpaidMonths ?? 0) === 1)
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Belum lunas 1 bulan
                    </div>
                @endif
                @if ($currentPayment ?? null)
                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-cream-100 px-4 py-3">
                        <div>
                            <p class="font-semibold text-teal-950">{{ $currentPayment['label'] }}</p>
                            <p class="text-xs text-slate-500">Status bulan ini</p>
                        </div>
                        <x-badge :tone="$currentPayment['paid'] ? 'ok' : 'warn'">
                            {{ $currentPayment['paid'] ? 'Lunas' : 'Belum bayar' }}
                        </x-badge>
                    </div>
                @endif
                <div class="space-y-2">
                    @foreach ($payments ?? [] as $row)
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-slate-600">{{ $row['label'] }}</span>
                            @if (! ($row['obligated'] ?? true))
                                <span class="text-slate-400">—</span>
                            @else
                                <span class="font-semibold {{ $row['paid'] ? 'text-teal-800' : 'text-amber-700' }}">
                                    {{ $row['paid'] ? 'Lunas' : 'Belum' }}
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>

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
                                        Setoran terakhir: {{ $needsFollowUp->passageLabel() }}.
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
                            @else
                                <p class="mt-2 font-display text-xl font-semibold text-slate-700">Belum dicatat</p>
                                <p class="mt-1 text-xs text-slate-500">Absensi hari ini belum diisi pengajar</p>
                            @endif
                        </div>
                        <div class="ui-card p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Setoran</p>
                            @if ($todaySetoran->isNotEmpty())
                                <p class="mt-2 font-display text-xl font-semibold text-teal-950">{{ $todaySetoran->first()->status->label() }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $todaySetoran->first()->passageLabel() }}</p>
                            @else
                                <p class="mt-2 font-display text-xl font-semibold text-slate-700">Belum ada</p>
                                <p class="mt-1 text-xs text-slate-500">Belum ada setoran tercatat hari ini</p>
                            @endif
                        </div>
                        <div class="ui-card p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Hari ini</p>
                            <p class="mt-2 font-display text-xl font-semibold text-teal-950">{{ $snapshot['dayLabel'] ?? '' }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ \App\Support\DateLabel::long(now()) }}</p>
                        </div>
                    </div>
                </section>
            @endif

            @if ($bacaanSnap || ($bacaan['iqroLabel'] ?? null) || ($hafalan['juz30'] ?? null) || ($hafalan['doaName'] ?? null))
                <section id="progress" class="scroll-mt-24 space-y-3">
                    <h2 class="ui-section-title px-1">Progress</h2>

                    @if ($bacaanSnap)
                        <x-card>
                            <p class="text-sm font-semibold text-slate-700">Bacaan Alquran</p>
                            <div class="mt-2 flex items-center justify-between gap-3 text-sm">
                                <p class="font-semibold text-teal-950">Juz {{ $bacaanSnap->juz->number }}</p>
                                <p class="text-slate-500">{{ $bacaanSnap->juz->lancarCount }}/{{ $bacaanSnap->juz->ayahTotal }} · {{ $bacaanSnap->percentLabel() }}</p>
                            </div>
                            <p class="mt-1 text-sm text-slate-600">{{ $bacaanSnap->positionLabel }}</p>
                            <x-progress class="mt-2" :value="$bacaanSnap->juz->percent" />
                        </x-card>
                    @elseif ($bacaan['iqroLabel'] ?? null)
                        <x-card>
                            <p class="text-sm font-semibold text-slate-700">Bacaan Iqro</p>
                            <p class="mt-2 font-display text-xl font-semibold text-teal-950">{{ $bacaan['iqroLabel'] }}</p>
                        </x-card>
                    @endif

                    @if ($hafalan['juz30'] ?? null)
                        <x-card>
                            <p class="text-sm font-semibold text-slate-700">Hafalan Juz 30</p>
                            <div class="mt-2 flex items-center justify-between gap-3 text-sm">
                                <p class="font-semibold text-teal-950">{{ $hafalan['juz30']->positionLabel }}</p>
                                <p class="text-slate-500">{{ $hafalan['juz30']->percentLabel() }}</p>
                            </div>
                            <x-progress class="mt-2" :value="$hafalan['juz30']->juz->percent" />
                        </x-card>
                    @endif

                    @if ($hafalan['doaName'] ?? null)
                        <x-card>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-700">Doa terakhir</p>
                                    <p class="mt-2 font-display text-xl font-semibold text-teal-950">{{ $hafalan['doaName'] }}</p>
                                </div>
                                @if ($hafalan['doaStatus'] ?? null)
                                    <x-badge :tone="$hafalan['doaStatus']->value === 'lulus' ? 'ok' : 'warn'">
                                        {{ $hafalan['doaStatus']->label() }}
                                    </x-badge>
                                @endif
                            </div>
                        </x-card>
                    @endif
                </section>
            @else
                <x-empty>Belum ada progress bacaan/hafalan.</x-empty>
            @endif

            <section id="setoran" class="scroll-mt-24 space-y-2">
                <h2 class="ui-section-title px-1">Setoran terakhir</h2>
                @forelse ($setoran as $item)
                    <div class="ui-card p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-teal-950">{{ $item->passageLabel() }}</p>
                                <p class="text-sm text-slate-500">{{ $dateLabel($item->setoran_date) }} · {{ $item->setoran_date->format('d/m/Y') }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $item->status->hint() }}</p>
                            </div>
                            <x-badge :tone="$item->status->value === 'lulus' ? 'ok' : 'warn'">
                                {{ $item->status->label() }}
                            </x-badge>
                        </div>
                    </div>
                @empty
                    <x-empty>Belum ada setoran. Pengajar akan mencatat setoran setelah pertemuan.</x-empty>
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
                            <p class="font-semibold text-teal-950">{{ \App\Support\DateLabel::long($row->session->session_date) }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $row->status->hint() }}</p>
                        </div>
                        <x-badge :tone="$row->status->value === 'hadir' ? 'ok' : ($row->status->value === 'alfa' ? 'danger' : ($row->status->value === 'izin' ? 'warn' : 'info'))">
                            {{ $row->status->label() }}
                        </x-badge>
                    </div>
                @empty
                    <x-empty>Belum ada absensi. Kehadiran muncul setelah absensi dicatat.</x-empty>
                @endforelse
            </section>
        @endunless
    </div>
</x-app-layout>
