@php
    $stats = $overview['stats'];
    $pendingCount = $overview['pendingSetoran']->count();
    $followUpCount = $overview['followUpSetoran']->count();
    $todaySession = $overview['todaySession'] ?? null;
    $attendancePending = $todaySession && ! ($overview['attendanceRecorded'] ?? false);
@endphp

{{-- 1. Absensi hari ini (aksi utama) --}}
<section class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <h2 class="ui-section-title">Absensi hari ini</h2>
        <a href="{{ route('ops.attendance.index') }}" class="text-sm font-semibold text-teal-800">Semua absensi</a>
    </div>

    @if ($overview['isOffDay'] ?? false)
        <x-empty>{{ $overview['offDayMessage'] }}</x-empty>
    @elseif ($todaySession)
        @php $todayDateLabel = \App\Support\DateLabel::long(now()); @endphp
        <div class="ui-card p-4 sm:p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="font-display text-lg font-semibold text-teal-950">{{ $todayDateLabel }}</p>
                        <x-badge tone="ok">Sesi terbuka</x-badge>
                    </div>
                </div>
                <a href="{{ route('ops.attendance.show', $todaySession) }}" class="btn-primary min-h-11">Isi absensi</a>
            </div>
        </div>
    @else
        <x-empty>Tidak ada sesi untuk {{ $overview['dayLabel'] }}.</x-empty>
    @endif
</section>

{{-- 2. Perlu perhatian --}}
<section class="space-y-3">
    <div class="flex items-center gap-2">
        <x-icon name="alert" class="h-4 w-4 text-amber-700" />
        <h2 class="ui-section-title">Perlu perhatian</h2>
    </div>

    @if ($overview['alfaToday']->isEmpty() && $overview['followUpSetoran']->isEmpty() && $overview['pendingSetoran']->isEmpty())
        <x-empty>Tidak ada alfa, setoran ulang, atau santri yang belum setor hari ini.</x-empty>
    @else
        @if ($overview['alfaToday']->isNotEmpty())
            <div class="ui-card p-4">
                <p class="text-sm font-semibold text-rose-800">Alfa hari ini · {{ $stats['alfaHariIni'] }}</p>
                <div class="mt-3 max-h-64 space-y-3 overflow-y-auto">
                @foreach ($overview['alfaToday'] as $row)
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-medium text-teal-950">{{ $row->santri->user->name }}</p>
                    </div>
                    <x-badge tone="danger">Alfa</x-badge>
                    </div>
                @endforeach
                </div>
            </div>
        @endif

        @if ($overview['followUpSetoran']->isNotEmpty() || $overview['pendingSetoran']->isNotEmpty())
            <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                <div class="ui-card overflow-hidden" data-queue="setoran-ulang">
                    <p class="border-b border-teal-950/5 px-4 py-3 text-sm font-semibold text-amber-800">Setoran perlu diulang · {{ $followUpCount }}</p>
                    <div class="max-h-64 space-y-3 overflow-y-auto px-4 py-3">
                        @forelse ($overview['followUpSetoran'] as $item)
                            <a href="{{ route('ops.setoran.edit', $item) }}" class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-medium text-teal-950">{{ $item->santri->user->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $item->passageLabel() }}</p>
                                </div>
                                <x-badge tone="warn">{{ $item->status->label() }}</x-badge>
                            </a>
                        @empty
                            <p class="text-sm text-slate-500">Tidak ada setoran yang perlu diulang.</p>
                        @endforelse
                    </div>
                </div>

                <div class="ui-card overflow-hidden" data-queue="belum-setor">
                    <p class="border-b border-teal-950/5 px-4 py-3 text-sm font-semibold text-slate-700">Belum setor hari ini · {{ $pendingCount }}</p>
                    <div class="max-h-64 space-y-3 overflow-y-auto px-4 py-3">
                        @forelse ($overview['pendingSetoran'] as $santri)
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-medium text-teal-950">{{ $santri->user->name }}</p>
                                </div>
                                <a href="{{ route('ops.setoran.create') }}" class="text-sm font-semibold text-teal-800">Input</a>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Semua santri sudah setor hari ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    @endif
</section>

{{-- 3. Snapshot angka hari ini --}}
<div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    <div class="stat-card">
        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-800"><x-icon name="users" /></span>
        <p class="mt-4 font-display text-3xl font-semibold text-teal-950">{{ $stats['santriAktif'] }}</p>
        <p class="text-sm text-slate-500">Santri aktif</p>
        <p class="mt-1 text-xs text-slate-400">{{ $stats['pengajar'] }} pengajar</p>
    </div>
    <div class="stat-card">
        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-50 text-sky-700"><x-icon name="book" /></span>
        <p class="mt-4 font-display text-3xl font-semibold text-teal-950">{{ $stats['setoranHariIni'] }}</p>
        <p class="text-sm text-slate-500">Setoran hari ini</p>
        @if ($stats['santriAktif'] > 0)
            <p class="mt-1 text-xs {{ $stats['sudahSetorHariIni'] < $stats['santriAktif'] ? 'text-amber-700' : 'text-slate-400' }}">
                {{ $stats['sudahSetorHariIni'] }}/{{ $stats['santriAktif'] }} sudah setor
            </p>
        @endif
        @if ($followUpCount > 0)
            <p class="mt-1 text-xs text-amber-700">{{ $followUpCount }} perlu diulang</p>
        @endif
    </div>
    <div class="stat-card {{ $attendancePending ? '' : 'border-rose-100 bg-rose-50/70' }}">
        <span class="flex h-10 w-10 items-center justify-center rounded-2xl {{ $attendancePending ? 'bg-slate-100 text-slate-500' : 'bg-white text-rose-700' }}"><x-icon name="clipboard" /></span>
        @if ($attendancePending)
            <p class="mt-4 font-display text-3xl font-semibold text-slate-400">—</p>
            <p class="text-sm text-slate-500">Belum diisi</p>
        @else
            <p class="mt-4 font-display text-3xl font-semibold text-rose-800">{{ $stats['alfaHariIni'] }}</p>
            <p class="text-sm text-rose-700">Alfa hari ini</p>
            <p class="mt-1 text-xs text-rose-600/80">{{ $stats['hadirHariIni'] }} hadir · {{ $stats['izinHariIni'] }} izin · {{ $stats['sakitHariIni'] }} sakit</p>
        @endif
    </div>
    <div class="stat-card">
        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-800"><x-icon name="calendar" /></span>
        <p class="mt-4 font-display text-3xl font-semibold text-teal-950">{{ $stats['sesiHariIni'] }}</p>
        <p class="text-sm text-slate-500">Sesi hari ini</p>
    </div>
</div>

@include('dashboard.partials.week')
