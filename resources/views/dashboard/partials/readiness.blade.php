<div class="relative overflow-hidden rounded-3xl border border-gold-200 bg-gold-50/80 p-5 shadow-soft sm:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gold-700">Persiapan</p>
            <h2 class="mt-1 font-display text-2xl font-semibold text-teal-950">Halaqah belum siap dipakai</h2>
            <p class="mt-2 max-w-xl text-sm text-slate-600">
                Isi tahun ajaran, lokasi, pengajar, santri, lalu satu jadwal. Satu halaman, selesai — pengajar bisa membuka absensi.
            </p>
        </div>
        <a href="{{ route('ketua.setup.create') }}" class="btn-primary min-h-11 shrink-0">Siapkan halaqah</a>
    </div>
    <ul class="mt-5 grid gap-2 sm:grid-cols-2">
        @foreach ($readiness['checks'] as $check)
            <li class="flex items-center gap-2 rounded-2xl bg-white/80 px-3 py-2 text-sm {{ $check['done'] ? 'text-teal-800' : 'text-slate-500' }}">
                <span class="flex h-6 w-6 items-center justify-center rounded-full {{ $check['done'] ? 'bg-teal-700 text-white' : 'bg-slate-200 text-slate-500' }}">
                    @if ($check['done'])
                        <x-icon name="check-circle" class="h-4 w-4" />
                    @else
                        <span class="text-[11px] font-semibold">{{ $loop->iteration }}</span>
                    @endif
                </span>
                {{ $check['label'] }}
            </li>
        @endforeach
    </ul>
</div>
