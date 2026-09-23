@php
    $stats = $overview['stats'];
    $pendingCount = $overview['pendingSetoran']->count();
    $followUpCount = $overview['followUpSetoran']->count();
    $week = $overview['week'];
@endphp

{{-- 1. Jadwal hari ini (aksi utama) --}}
<section class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <h2 class="ui-section-title">Jadwal hari ini</h2>
        <a href="{{ route('ops.attendance.index') }}" class="text-sm font-semibold text-teal-800">Semua absensi</a>
    </div>

    @if ($overview['isOffDay'] ?? false)
        <x-empty>{{ $overview['offDayMessage'] }}</x-empty>
    @else
        @php
            $showUstazOnSlots = $overview['todaySlots']->count() > 1
                && Auth::user()->hasAnyRole([\App\Support\Role::Ketua, \App\Support\Role::KetuaPengajar]);
            $todayDateLabel = \App\Support\DateLabel::long(now());
        @endphp
        @forelse ($overview['todaySlots'] as $slot)
            @php $openSession = $slot->sessions->first(); @endphp
            <div class="ui-card p-4 sm:p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-display text-lg font-semibold text-teal-950">{{ $todayDateLabel }}</p>
                            @if ($openSession)
                                <x-badge tone="ok">Sesi terbuka</x-badge>
                            @endif
                        </div>
                        @if ($showUstazOnSlots)
                            <p class="text-sm text-slate-500">{{ $slot->halaqah->ustaz->name }}</p>
                        @endif
                    </div>
                    @if ($openSession)
                        <a href="{{ route('ops.attendance.show', $openSession) }}" class="btn-primary min-h-11">Isi absensi</a>
                    @endif
                </div>
            </div>
        @empty
            <x-empty>Tidak ada jadwal untuk {{ $overview['dayLabel'] }}.</x-empty>
        @endforelse
    @endif
</section>

{{-- 2. Perlu perhatian (exception saja) --}}
<section class="space-y-3">
    <div class="flex items-center gap-2">
        <x-icon name="alert" class="h-4 w-4 text-amber-700" />
        <h2 class="ui-section-title">Perlu perhatian</h2>
    </div>

    @if ($overview['alfaToday']->isEmpty() && $overview['followUpSetoran']->isEmpty() && $overview['pendingSetoran']->isEmpty())
        <x-empty>Tidak ada alfa, setoran ulang, atau santri yang belum setor hari ini.</x-empty>
    @else
        @if ($overview['alfaToday']->isNotEmpty())
            <div class="ui-card space-y-3 p-4">
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
            <div class="ui-card space-y-3 p-4">
                <p class="text-sm font-semibold text-amber-800">Setoran perlu diulang · {{ $followUpCount }}</p>
                @foreach ($overview['followUpSetoran'] as $item)
                    <a href="{{ route('ops.setoran.edit', $item) }}" class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-medium text-teal-950">{{ $item->santri->user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $item->passageLabel() }}</p>
                        </div>
                        <x-badge tone="warn">{{ $item->status->label() }}</x-badge>
                    </a>
                @endforeach
            </div>
        @endif

        @if ($overview['pendingSetoran']->isNotEmpty())
            <div class="ui-card space-y-3 p-4">
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

{{-- 3. Snapshot angka hari ini --}}
<div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    @if ($isKetua)
        <div class="stat-card">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-800"><x-icon name="layers" /></span>
            <p class="mt-4 font-display text-3xl font-semibold text-teal-950">{{ $stats['halaqah'] }}</p>
            <p class="text-sm text-slate-500">Kelas aktif</p>
            <p class="mt-1 text-xs text-slate-400">{{ $stats['ustaz'] }} ustaz · {{ $stats['santriAktif'] }} santri</p>
        </div>
    @else
        <div class="stat-card">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gold-50 text-gold-700"><x-icon name="users" /></span>
            <p class="mt-4 font-display text-3xl font-semibold text-teal-950">{{ $stats['santriAktif'] }}</p>
            <p class="text-sm text-slate-500">Anggota aktif</p>
            <p class="mt-1 text-xs text-slate-400">{{ $stats['halaqah'] }} kelas dibimbing</p>
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
    <div class="stat-card">
        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-800"><x-icon name="calendar" /></span>
        <p class="mt-4 font-display text-3xl font-semibold text-teal-950">{{ $stats['sesiTerbuka'] }}/{{ $stats['slotHariIni'] }}</p>
        <p class="text-sm text-slate-500">Sesi hari ini</p>
        <p class="mt-1 text-xs text-slate-400">terbuka / slot jadwal</p>
    </div>
</div>

{{-- 4. SPP (Ketua saja) --}}
@if ($isKetua && ($sppSummary ?? null))
    <section class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <h2 class="ui-section-title">Ringkasan SPP</h2>
            <a href="{{ route('ops.spp.index') }}" class="text-sm font-semibold text-teal-800">Kelola SPP</a>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            <div class="ui-card p-4">
                <p class="text-xs text-slate-500">Sudah bayar bulan ini</p>
                <p class="mt-1 font-display text-2xl font-semibold text-teal-800">
                    {{ $sppSummary['paidThisMonth'] }}/{{ $sppSummary['paidThisMonth'] + $sppSummary['unpaidThisMonth'] }}
                </p>
            </div>
            <div class="ui-card p-4">
                <p class="text-xs text-slate-500">Belum bayar bulan ini</p>
                <p class="mt-1 font-display text-2xl font-semibold {{ $sppSummary['unpaidThisMonth'] > 0 ? 'text-amber-800' : 'text-teal-950' }}">
                    {{ $sppSummary['unpaidThisMonth'] }}
                </p>
                @if ($sppSummary['unpaidThisMonth'] > 0)
                    <p class="mt-1 text-xs text-slate-500">Rp {{ number_format($sppSummary['unpaidThisMonthAmount'], 0, ',', '.') }}</p>
                @endif
            </div>
            <a href="{{ route('ops.spp.index', ['filter' => 'nunggak']) }}" class="ui-card p-4">
                <p class="text-xs text-slate-500">Nunggak &gt;1 bulan</p>
                <p class="mt-1 font-display text-2xl font-semibold {{ $sppSummary['deepArrears'] > 0 ? 'text-rose-800' : 'text-teal-950' }}">
                    {{ $sppSummary['deepArrears'] }}
                </p>
                <p class="mt-1 text-xs text-teal-800">Lihat daftar</p>
            </a>
        </div>
    </section>
@endif

{{-- 5. Minggu ini (ringkas, tanpa daftar nama) --}}
<section class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <h2 class="ui-section-title">Minggu ini</h2>
        <a href="{{ route('laporan.attendance.index', ['date_from' => $week['from'], 'date_to' => $week['to']]) }}" class="text-sm font-semibold text-teal-800">
            Rekap minggu ini
        </a>
    </div>
    <div class="ui-card p-4 sm:p-5">
        <p class="text-xs text-slate-500">{{ $week['fromLabel'] }}–{{ $week['toLabel'] }}</p>
        <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
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
        @if ($week['total'] === 0)
            <p class="mt-3 text-sm text-slate-500">Belum ada absensi dari Senin sampai hari ini.</p>
        @endif
    </div>
</section>

{{-- 6. Kelas --}}
@if ($overview['halaqahRows']->isNotEmpty())
    <section class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <h2 class="ui-section-title">{{ $isKetua ? 'Kelas aktif' : 'Kelas Anda' }}</h2>
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
                    <p class="text-sm text-slate-500">{{ $row['halaqah']->ustaz->name }}</p>
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
