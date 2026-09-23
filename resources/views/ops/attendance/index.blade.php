<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Operasional</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Absensi</h1>
            <p class="text-sm text-slate-500">{{ $todayDateLabel }} · Senin–Jumat</p>
        </div>
    </x-slot>

    <div class="max-w-2xl space-y-6">
        <section class="space-y-3">
            <h2 class="ui-section-title px-1">Hari ini</h2>

            @if ($isOffDay ?? false)
                <x-empty>{{ $offDayMessage }}</x-empty>
            @elseif (! ($hasActiveSantri ?? false))
                <x-empty>Belum ada santri aktif. Tambah santri di menu Santri terlebih dahulu.</x-empty>
            @elseif ($todaySession)
                <div class="ui-card space-y-4 p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-display text-lg font-semibold text-teal-950">{{ $todayDateLabel }}</p>
                            <p class="text-sm text-slate-500">Semua santri aktif</p>
                        </div>
                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-800">
                            <x-icon name="calendar" />
                        </span>
                    </div>
                    <a href="{{ route('ops.attendance.show', $todaySession) }}" class="btn-primary btn-block">
                        Isi absensi
                    </a>
                </div>
            @else
                <x-empty>Tidak ada sesi untuk {{ $dayLabel }}.</x-empty>
            @endif
        </section>

        <section class="space-y-3">
            <h2 class="ui-section-title px-1">Riwayat sesi</h2>
            @forelse ($recent as $item)
                <a href="{{ route('ops.attendance.show', $item) }}" class="ui-card block p-4 transition hover:-translate-y-0.5 hover:shadow-lift">
                    <p class="font-semibold text-teal-950">{{ \App\Support\DateLabel::long($item->session_date) }}</p>
                </a>
            @empty
                <p class="px-1 text-sm text-slate-500">Belum ada sesi absensi sebelumnya.</p>
            @endforelse
        </section>
    </div>
</x-app-layout>
