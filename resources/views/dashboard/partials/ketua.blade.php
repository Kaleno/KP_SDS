<section class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <h2 class="ui-section-title">Santri</h2>
        <a href="{{ route('ketua.santri.index') }}" class="text-sm font-semibold text-teal-800">Daftar santri</a>
    </div>
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <a href="{{ route('ketua.santri.index', ['status' => 'aktif']) }}" class="stat-card block">
            <p class="font-display text-3xl font-semibold text-teal-950">{{ $santriCensus['aktif'] }}</p>
            <p class="text-sm text-slate-500">Santri aktif</p>
        </a>
        <div class="stat-card">
            <p class="font-display text-3xl font-semibold text-teal-950">{{ $santriCensus['perempuan'] }}</p>
            <p class="text-sm text-slate-500">Perempuan</p>
            <p class="mt-1 text-xs text-slate-400">Dari santri aktif</p>
        </div>
        <div class="stat-card">
            <p class="font-display text-3xl font-semibold text-teal-950">{{ $santriCensus['lakiLaki'] }}</p>
            <p class="text-sm text-slate-500">Laki-laki</p>
            <p class="mt-1 text-xs text-slate-400">Dari santri aktif</p>
        </div>
        <a href="{{ route('ketua.santri.index', ['status' => 'lulus']) }}" class="stat-card block">
            <p class="font-display text-3xl font-semibold text-teal-950">{{ $santriCensus['lulus'] }}</p>
            <p class="text-sm text-slate-500">Lulus</p>
        </a>
    </div>
</section>

@if ($sppSummary ?? null)
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
            <a href="{{ route('ops.spp.index', ['filter' => 'nunggak']) }}" class="ui-card col-span-2 p-4 sm:col-span-1">
                <p class="text-xs text-slate-500">Nunggak &gt;1 bulan</p>
                <p class="mt-1 font-display text-2xl font-semibold {{ $sppSummary['deepArrears'] > 0 ? 'text-rose-800' : 'text-teal-950' }}">
                    {{ $sppSummary['deepArrears'] }}
                </p>
                <p class="mt-1 text-xs text-teal-800">Lihat daftar</p>
            </a>
        </div>
    </section>
@endif

@include('dashboard.partials.week')

<section class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <h2 class="ui-section-title">Keuangan</h2>
        <a href="{{ route('ketua.finance.index') }}" class="text-sm font-semibold text-teal-800">Kas DKM</a>
    </div>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="ui-card p-4">
            <p class="text-xs text-slate-500">Saldo</p>
            <p class="mt-1 font-display text-2xl font-semibold text-teal-950">{{ number_format($finance['saldo'], 0, ',', '.') }}</p>
        </div>
        <div class="ui-card p-4">
            <p class="text-xs text-slate-500">Pemasukan</p>
            <p class="mt-1 font-display text-2xl font-semibold text-teal-800">{{ number_format($finance['pemasukan'], 0, ',', '.') }}</p>
        </div>
        <div class="ui-card p-4">
            <p class="text-xs text-slate-500">Pengeluaran</p>
            <p class="mt-1 font-display text-2xl font-semibold {{ $finance['pengeluaran'] > 0 ? 'text-rose-800' : 'text-teal-950' }}">{{ number_format($finance['pengeluaran'], 0, ',', '.') }}</p>
        </div>
    </div>
</section>
