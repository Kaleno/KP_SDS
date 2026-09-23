<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Operasional</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Absensi</h1>
            <p class="text-sm text-slate-500">{{ $todayDateLabel }}</p>
        </div>
    </x-slot>

    <div class="max-w-2xl space-y-6">
        <section class="space-y-3">
            <h2 class="ui-section-title px-1">Jadwal hari ini</h2>

            @if ($isOffDay ?? false)
                <x-empty>{{ $offDayMessage }}</x-empty>
            @else
                @forelse ($todaySlots as $slot)
                    @php $openSession = $slot->sessions->first(); @endphp
                    <div class="ui-card space-y-4 p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-display text-lg font-semibold text-teal-950">{{ $todayDateLabel }}</p>
                                @if ($showUstazOnSlots ?? false)
                                    <p class="text-sm text-slate-500">{{ $slot->halaqah->ustaz->name }}</p>
                                @endif
                            </div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-800">
                                <x-icon name="calendar" />
                            </span>
                        </div>
                        @if ($openSession)
                            <a href="{{ route('ops.attendance.show', $openSession) }}" class="btn-primary btn-block">
                                Isi absensi
                            </a>
                        @endif
                    </div>
                @empty
                    <x-empty>
                        @if (! ($hasAssignedHalaqah ?? true))
                            Belum ada kelas yang ditugaskan kepada Anda. Minta Ketua menugaskan Anda sebagai pengajar di menu Kelas.
                        @else
                            Tidak ada jadwal untuk {{ $dayLabel }}. Minta Ketua menambahkan jadwal kelas di hari ini.
                        @endif
                    </x-empty>
                @endforelse
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
