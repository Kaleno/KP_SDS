<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Operasional</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Absensi</h1>
            <p class="text-sm text-slate-500">Hari ini {{ $dayLabel }}</p>
        </div>
    </x-slot>

    <div class="max-w-2xl space-y-6">
        <section class="space-y-3">
            <h2 class="ui-section-title px-1">Slot hari ini</h2>

            @forelse ($todaySlots as $slot)
                @php $openSession = $slot->sessions->first(); @endphp
                <div class="ui-card p-5 space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-display text-lg font-semibold text-teal-950">{{ $slot->halaqah->name }}</p>
                            <p class="text-sm text-slate-500">{{ $slot->timeRange() }} · {{ $slot->location->name }}</p>
                        </div>
                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-800">
                            <x-icon name="calendar" />
                        </span>
                    </div>
                    @if ($openSession)
                        <a href="{{ route('ops.attendance.show', $openSession) }}" class="btn-primary btn-block">
                            Isi absensi
                        </a>
                    @else
                        <form method="POST" action="{{ route('ops.attendance.open', $slot) }}">
                            @csrf
                            <button type="submit" class="btn-primary btn-block">Buka sesi</button>
                        </form>
                    @endif
                </div>
            @empty
                <x-empty>
                    Tidak ada slot jadwal untuk {{ $dayLabel }}. Minta Ketua menambahkan jadwal halaqah di hari ini.
                </x-empty>
            @endforelse
        </section>

        <section class="space-y-3">
            <h2 class="ui-section-title px-1">Riwayat sesi</h2>
            @forelse ($recent as $item)
                <a href="{{ route('ops.attendance.show', $item) }}" class="ui-card block p-4 transition hover:-translate-y-0.5 hover:shadow-lift">
                    <p class="font-semibold text-teal-950">{{ $item->schedule->halaqah->name }}</p>
                    <p class="text-sm text-slate-500">
                        {{ $item->session_date->format('d/m/Y') }} · {{ $item->schedule->timeRange() }}
                    </p>
                </a>
            @empty
                <p class="text-sm text-slate-500 px-1">Belum ada sesi absensi.</p>
            @endforelse
        </section>
    </div>
</x-app-layout>
