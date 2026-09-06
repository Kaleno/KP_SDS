<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Absensi</h1>
        <p class="text-sm text-slate-500">Hari ini {{ $dayLabel }}</p>
    </x-slot>

    <div class="max-w-lg space-y-6">
        <section class="space-y-3">
            <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide">Slot hari ini</h2>

            @forelse ($todaySlots as $slot)
                @php $openSession = $slot->sessions->first(); @endphp
                <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-3">
                    <div>
                        <p class="font-semibold text-slate-800">{{ $slot->halaqah->name }}</p>
                        <p class="text-sm text-slate-500">{{ $slot->timeRange() }} · {{ $slot->location->name }}</p>
                    </div>
                    @if ($openSession)
                        <a href="{{ route('ops.attendance.show', $openSession) }}"
                           class="flex items-center justify-center w-full min-h-12 rounded-xl bg-teal-700 text-white font-semibold text-base">
                            Isi absensi
                        </a>
                    @else
                        <form method="POST" action="{{ route('ops.attendance.open', $slot) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full min-h-12 rounded-xl bg-teal-700 text-white font-semibold text-base">
                                Buka sesi
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200 p-5 text-sm text-slate-600">
                    Tidak ada slot jadwal untuk {{ $dayLabel }}. Minta Ketua menambahkan jadwal halaqah di hari ini.
                </div>
            @endforelse
        </section>

        <section class="space-y-3">
            <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide">Riwayat sesi</h2>
            @forelse ($recent as $item)
                <a href="{{ route('ops.attendance.show', $item) }}"
                   class="block bg-white rounded-2xl border border-slate-200 p-4">
                    <p class="font-medium text-slate-800">{{ $item->schedule->halaqah->name }}</p>
                    <p class="text-sm text-slate-500">
                        {{ $item->session_date->format('d/m/Y') }} · {{ $item->schedule->timeRange() }}
                    </p>
                </a>
            @empty
                <p class="text-sm text-slate-500">Belum ada sesi absensi.</p>
            @endforelse
        </section>
    </div>
</x-app-layout>
