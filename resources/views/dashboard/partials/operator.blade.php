@php
    $stats = $overview['stats'];
    $attendanceTotal = $stats['hadirHariIni'] + $stats['izinHariIni'] + $stats['sakitHariIni'] + $stats['alfaHariIni'];
    $pendingCount = $overview['pendingSetoran']->count();
    $followUpCount = $overview['followUpSetoran']->count();
@endphp

<div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    @if ($isKetua)
        <div class="stat-card">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-800"><x-icon name="layers" /></span>
            <p class="mt-4 font-display text-3xl font-semibold text-teal-950">{{ $stats['halaqah'] }}</p>
            <p class="text-sm text-slate-500">Halaqah aktif</p>
            <p class="mt-1 text-xs text-slate-400">{{ $stats['ustaz'] }} ustaz pembimbing</p>
        </div>
        <div class="stat-card">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gold-50 text-gold-700"><x-icon name="users" /></span>
            <p class="mt-4 font-display text-3xl font-semibold text-teal-950">{{ $stats['santriAktif'] }}</p>
            <p class="text-sm text-slate-500">Santri aktif</p>
            <p class="mt-1 text-xs text-slate-400">{{ $stats['anggotaHalaqah'] }} anggota halaqah</p>
        </div>
    @else
        <div class="stat-card">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gold-50 text-gold-700"><x-icon name="users" /></span>
            <p class="mt-4 font-display text-3xl font-semibold text-teal-950">{{ $stats['santriAktif'] }}</p>
            <p class="text-sm text-slate-500">Anggota aktif</p>
            <p class="mt-1 text-xs text-slate-400">{{ $stats['halaqah'] }} halaqah dibimbing</p>
        </div>
        <div class="stat-card">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-800"><x-icon name="calendar" /></span>
            <p class="mt-4 font-display text-3xl font-semibold text-teal-950">{{ $stats['sesiTerbuka'] }}/{{ $stats['slotHariIni'] }}</p>
            <p class="text-sm text-slate-500">Sesi hari ini</p>
            <p class="mt-1 text-xs text-slate-400">terbuka / slot jadwal</p>
        </div>
    @endif
    <div class="stat-card">
        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-50 text-sky-700"><x-icon name="book" /></span>
        <p class="mt-4 font-display text-3xl font-semibold text-teal-950">{{ $stats['setoranHariIni'] }}</p>
        <p class="text-sm text-slate-500">Setoran hari ini</p>
        @if ($stats['anggotaHalaqah'] > 0)
            <p class="mt-1 text-xs {{ $stats['sudahSetorHariIni'] < $stats['anggotaHalaqah'] ? 'text-amber-700' : 'text-slate-400' }}">
                {{ $stats['sudahSetorHariIni'] }}/{{ $stats['anggotaHalaqah'] }} anggota sudah setor
            </p>
        @endif
        @if ($followUpCount > 0)
            <p class="mt-1 text-xs text-amber-700">{{ $followUpCount }} perlu diulang</p>
        @endif
    </div>
    <div class="stat-card border-rose-100 bg-rose-50/70">
        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-rose-700"><x-icon name="clipboard" /></span>
        <p class="mt-4 font-display text-3xl font-semibold text-rose-800">{{ $stats['alfaHariIni'] }}</p>
        <p class="text-sm text-rose-700">Alfa hari ini</p>
        <p class="mt-1 text-xs text-rose-600/80">{{ $stats['hadirHariIni'] }} hadir · {{ $stats['izinHariIni'] }} izin · {{ $stats['sakitHariIni'] }} sakit</p>
    </div>
</div>

@if ($attendanceTotal > 0 || ($isKetua && $stats['slotHariIni'] > 0))
    <div class="ui-card p-4 sm:p-5">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="ui-section-title">Kehadiran hari ini</h2>
            <p class="text-xs text-slate-500">
                @if ($attendanceTotal > 0)
                    {{ $attendanceTotal }} tercatat
                @else
                    Sesi belum diisi
                @endif
                @if ($isKetua)
                    · {{ $stats['sesiTerbuka'] }}/{{ $stats['slotHariIni'] }} sesi terbuka
                @endif
            </p>
        </div>
        @if ($attendanceTotal > 0)
            <div class="mt-3 flex h-2.5 overflow-hidden rounded-full bg-cream-100">
                @if ($stats['hadirHariIni'] > 0)
                    <span class="bg-teal-700" style="flex: {{ $stats['hadirHariIni'] }} 1 0%" title="Hadir"></span>
                @endif
                @if ($stats['izinHariIni'] > 0)
                    <span class="bg-amber-400" style="flex: {{ $stats['izinHariIni'] }} 1 0%" title="Izin"></span>
                @endif
                @if ($stats['sakitHariIni'] > 0)
                    <span class="bg-sky-400" style="flex: {{ $stats['sakitHariIni'] }} 1 0%" title="Sakit"></span>
                @endif
                @if ($stats['alfaHariIni'] > 0)
                    <span class="bg-rose-500" style="flex: {{ $stats['alfaHariIni'] }} 1 0%" title="Alfa"></span>
                @endif
            </div>
        @endif
        <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
            <div class="rounded-xl bg-teal-50 px-3 py-2">
                <p class="text-lg font-semibold text-teal-800">{{ $stats['hadirHariIni'] }}</p>
                <p class="text-xs text-teal-700">Hadir</p>
            </div>
            <div class="rounded-xl bg-amber-50 px-3 py-2">
                <p class="text-lg font-semibold text-amber-800">{{ $stats['izinHariIni'] }}</p>
                <p class="text-xs text-amber-700">Izin</p>
            </div>
            <div class="rounded-xl bg-sky-50 px-3 py-2">
                <p class="text-lg font-semibold text-sky-800">{{ $stats['sakitHariIni'] }}</p>
                <p class="text-xs text-sky-700">Sakit</p>
            </div>
            <div class="rounded-xl bg-rose-50 px-3 py-2">
                <p class="text-lg font-semibold text-rose-800">{{ $stats['alfaHariIni'] }}</p>
                <p class="text-xs text-rose-700">Alfa</p>
            </div>
        </div>
    </div>
@endif

@php $week = $overview['week']; @endphp
<section class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <h2 class="ui-section-title">Minggu ini</h2>
        <a href="{{ route('laporan.attendance.index', ['date_from' => $week['from'], 'date_to' => $week['to']]) }}" class="text-sm font-semibold text-teal-800">
            Rekap {{ $week['fromLabel'] }}–{{ $week['toLabel'] }}
        </a>
    </div>
    <div class="ui-card p-4 sm:p-5">
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
            <div class="rounded-xl bg-teal-50 px-3 py-2">
                <p class="text-lg font-semibold text-teal-800">{{ $week['hadir'] }}</p>
                <p class="text-xs text-teal-700">Hadir</p>
            </div>
            <div class="rounded-xl bg-amber-50 px-3 py-2">
                <p class="text-lg font-semibold text-amber-800">{{ $week['izin'] }}</p>
                <p class="text-xs text-amber-700">Izin</p>
            </div>
            <div class="rounded-xl bg-sky-50 px-3 py-2">
                <p class="text-lg font-semibold text-sky-800">{{ $week['sakit'] }}</p>
                <p class="text-xs text-sky-700">Sakit</p>
            </div>
            <div class="rounded-xl bg-rose-50 px-3 py-2">
                <p class="text-lg font-semibold text-rose-800">{{ $week['alfa'] }}</p>
                <p class="text-xs text-rose-700">Alfa</p>
            </div>
        </div>
        @if ($week['alfaNames']->isNotEmpty())
            <div class="mt-4 space-y-2">
                <p class="text-sm font-semibold text-rose-800">Alfa minggu ini</p>
                @foreach ($week['alfaNames'] as $row)
                    <p class="text-sm text-slate-600">{{ $row->santri->user->name }} · {{ $row->session->session_date->format('d/m') }}</p>
                @endforeach
            </div>
        @endif
        @if ($week['missing']->isNotEmpty())
            <div class="mt-4 space-y-2">
                <p class="text-sm font-semibold text-slate-700">Belum tercatat minggu ini</p>
                @foreach ($week['missing'] as $santri)
                    <p class="text-sm text-slate-600">{{ $santri->user->name }}</p>
                @endforeach
            </div>
        @endif
        @if ($week['total'] === 0)
            <p class="mt-3 text-sm text-slate-500">Belum ada absensi dari Senin sampai hari ini.</p>
        @endif
    </div>
</section>

<section class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <h2 class="ui-section-title">Slot {{ $overview['dayLabel'] }}</h2>
        <a href="{{ route('ops.attendance.index') }}" class="text-sm font-semibold text-teal-800">Semua absensi</a>
    </div>

    @forelse ($overview['todaySlots'] as $slot)
        @php $openSession = $slot->sessions->first(); @endphp
        <div class="ui-card p-4 sm:p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="font-display text-lg font-semibold text-teal-950">{{ $slot->halaqah->name }}</p>
                        @if ($openSession)
                            <x-badge tone="ok">Sesi terbuka</x-badge>
                        @else
                            <x-badge tone="warn">Belum dibuka</x-badge>
                        @endif
                    </div>
                    <p class="text-sm text-slate-500">
                        {{ $slot->timeRange() }} · {{ $slot->location->name }}
                        @if ($isKetua)
                            · Ustaz {{ $slot->halaqah->ustaz->name }}
                        @endif
                    </p>
                </div>
                @if ($openSession)
                    <a href="{{ route('ops.attendance.show', $openSession) }}" class="btn-primary min-h-11">Isi absensi</a>
                @else
                    <form method="POST" action="{{ route('ops.attendance.open', $slot) }}">
                        @csrf
                        <button type="submit" class="btn-secondary min-h-11 w-full sm:w-auto">Buka sesi</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <x-empty>Tidak ada slot jadwal untuk {{ $overview['dayLabel'] }}.</x-empty>
    @endforelse
</section>

<div class="grid gap-4 lg:grid-cols-2">
    <section class="space-y-3">
        <div class="flex items-center gap-2">
            <x-icon name="alert" class="h-4 w-4 text-amber-700" />
            <h2 class="ui-section-title">Perlu perhatian</h2>
        </div>

        @if ($overview['alfaToday']->isEmpty() && $overview['followUpSetoran']->isEmpty() && $overview['pendingSetoran']->isEmpty())
            <x-empty>Tidak ada alfa, setoran ulang, atau santri yang belum setor hari ini.</x-empty>
        @else
            @if ($overview['alfaToday']->isNotEmpty())
                <div class="ui-card p-4 space-y-3">
                    <p class="text-sm font-semibold text-rose-800">Alfa hari ini · {{ $overview['alfaToday']->count() }}</p>
                    @foreach ($overview['alfaToday'] as $row)
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-medium text-teal-950">{{ $row->santri->user->name }}</p>
                                <p class="text-xs text-slate-500">{{ $row->session->schedule->halaqah->name }}</p>
                            </div>
                            <x-badge tone="danger">Alfa</x-badge>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($overview['followUpSetoran']->isNotEmpty())
                <div class="ui-card p-4 space-y-3">
                    <p class="text-sm font-semibold text-amber-800">Setoran perlu diulang · {{ $followUpCount }}</p>
                    @foreach ($overview['followUpSetoran'] as $item)
                        <a href="{{ route('ops.setoran.edit', $item) }}" class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-medium text-teal-950">{{ $item->santri->user->name }}</p>
                                <p class="text-xs text-slate-500">{{ $item->surah->name_id }} ayat {{ $item->ayahRange() }}</p>
                            </div>
                            <x-badge tone="warn">{{ $item->status->label() }}</x-badge>
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($overview['pendingSetoran']->isNotEmpty())
                <div class="ui-card p-4 space-y-3">
                    <p class="text-sm font-semibold text-slate-700">Belum setor hari ini · {{ $pendingCount }}</p>
                    @foreach ($overview['pendingSetoran'] as $santri)
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-medium text-teal-950">{{ $santri->user->name }}</p>
                                <p class="text-xs text-slate-500">{{ $santri->memberships->first()?->halaqah->name }}</p>
                            </div>
                            <a href="{{ route('ops.setoran.create') }}" class="text-sm font-semibold text-teal-800">Input</a>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </section>

    <section class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <h2 class="ui-section-title">Setoran terkini</h2>
            <a href="{{ route('ops.setoran.index') }}" class="text-sm font-semibold text-teal-800">Riwayat</a>
        </div>
        @forelse ($overview['recentSetoran'] as $item)
            <a href="{{ route('ops.setoran.edit', $item) }}" class="ui-card flex items-start justify-between gap-3 p-4">
                <div>
                    <p class="font-semibold text-teal-950">{{ $item->santri->user->name }}</p>
                    <p class="text-sm text-slate-600">{{ $item->surah->name_id }} ayat {{ $item->ayahRange() }}</p>
                    <p class="text-xs text-slate-500">{{ $item->setoran_date->format('d/m/Y') }} · {{ $item->halaqah->name }}</p>
                </div>
                <x-badge :tone="$item->status->value === 'lancar' ? 'ok' : ($item->status->value === 'ulang' ? 'warn' : 'info')">
                    {{ $item->status->label() }}
                </x-badge>
            </a>
        @empty
            <x-empty>Belum ada setoran pada tahun ajaran aktif.</x-empty>
        @endforelse
    </section>
</div>

@if ($overview['halaqahRows']->isNotEmpty())
    <section class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <h2 class="ui-section-title">{{ $isKetua ? 'Halaqah aktif' : 'Halaqah Anda' }}</h2>
            @if ($isKetua)
                <a href="{{ route('ketua.halaqah.index') }}" class="text-sm font-semibold text-teal-800">Kelola</a>
            @else
                <a href="{{ route('laporan.progress.index') }}" class="text-sm font-semibold text-teal-800">Progress juz</a>
            @endif
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
            @foreach ($overview['halaqahRows'] as $row)
                <div class="ui-card p-4 sm:p-5">
                    <p class="font-display text-lg font-semibold text-teal-950">{{ $row['halaqah']->name }}</p>
                    <p class="text-sm text-slate-500">Ustaz {{ $row['halaqah']->ustaz->name }}</p>
                    <div class="mt-4 grid grid-cols-3 gap-2 text-center text-sm">
                        <div class="rounded-xl bg-cream-100 py-2">
                            <p class="font-semibold text-teal-950">{{ $row['members'] }}</p>
                            <p class="text-[11px] text-slate-500">Anggota</p>
                        </div>
                        <div class="rounded-xl bg-teal-50 py-2">
                            <p class="font-semibold text-teal-800">{{ $row['setoranToday'] }}</p>
                            <p class="text-[11px] text-teal-700">Setoran</p>
                        </div>
                        <div class="rounded-xl bg-rose-50 py-2">
                            <p class="font-semibold text-rose-800">{{ $row['alfaToday'] }}</p>
                            <p class="text-[11px] text-rose-700">Alfa</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif

<div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
    <a class="ui-card group flex min-h-16 items-center justify-between px-5 py-4 transition hover:-translate-y-0.5 hover:shadow-lift" href="{{ route('laporan.progress.index') }}">
        <span class="flex items-center gap-3 font-semibold text-teal-950"><span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-800"><x-icon name="chart" /></span>Progress juz</span>
        <x-icon name="arrow-right" class="h-4 w-4 text-slate-400 group-hover:text-teal-800" />
    </a>
    <a class="ui-card group flex min-h-16 items-center justify-between px-5 py-4 transition hover:-translate-y-0.5 hover:shadow-lift" href="{{ route('laporan.attendance.index', ['date_from' => $overview['week']['from'], 'date_to' => $overview['week']['to']]) }}">
        <span class="flex items-center gap-3 font-semibold text-teal-950"><span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-800"><x-icon name="clipboard" /></span>Rekap minggu ini</span>
        <x-icon name="arrow-right" class="h-4 w-4 text-slate-400 group-hover:text-teal-800" />
    </a>
    <a class="ui-card group flex min-h-16 items-center justify-between px-5 py-4 transition hover:-translate-y-0.5 hover:shadow-lift" href="{{ route('ops.attendance.index') }}">
        <span class="flex items-center gap-3 font-semibold text-teal-950"><span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-800"><x-icon name="check-circle" /></span>Absensi hari ini</span>
        <x-icon name="arrow-right" class="h-4 w-4 text-slate-400 group-hover:text-teal-800" />
    </a>
    <a class="ui-card group flex min-h-16 items-center justify-between px-5 py-4 transition hover:-translate-y-0.5 hover:shadow-lift" href="{{ route('ops.setoran.create') }}">
        <span class="flex items-center gap-3 font-semibold text-teal-950"><span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gold-50 text-gold-700"><x-icon name="plus" /></span>Input setoran</span>
        <x-icon name="arrow-right" class="h-4 w-4 text-slate-400 group-hover:text-teal-800" />
    </a>
</div>
