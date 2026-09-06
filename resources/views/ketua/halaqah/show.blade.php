<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold text-slate-800">{{ $halaqah->name }}</h1>
                <p class="text-sm text-slate-500">{{ $halaqah->academicYear->name }} · Ustaz {{ $halaqah->ustaz->name }}</p>
            </div>
            <a href="{{ route('ketua.halaqah.edit', $halaqah) }}" class="text-sm font-medium text-teal-700">Ubah</a>
        </div>
    </x-slot>

    <div class="max-w-5xl space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-semibold">Anggota aktif</h2>
            <form method="POST" action="{{ route('ketua.halaqah.members.store', $halaqah) }}" class="mt-4 grid gap-3 sm:grid-cols-3">
                @csrf
                <select name="santri_id" class="border-gray-300 rounded-md shadow-sm" required>
                    <option value="">Pilih santri</option>
                    @foreach ($availableSantri as $santri)
                        <option value="{{ $santri->id }}">{{ $santri->nis }} — {{ $santri->user->name }}</option>
                    @endforeach
                </select>
                <x-text-input type="date" name="started_at" :value="old('started_at', now()->toDateString())" required />
                <x-primary-button>Tambah anggota</x-primary-button>
            </form>
            <x-input-error class="mt-2" :messages="$errors->get('santri_id')" />

            <ul class="mt-4 divide-y divide-slate-100">
                @forelse ($halaqah->activeMembers as $member)
                    <li class="py-4 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <p class="font-medium">{{ $member->santri->user->name }}</p>
                                <p class="text-sm text-slate-500">NIS {{ $member->santri->nis }} · masuk {{ $member->started_at->format('d/m/Y') }}</p>
                            </div>
                            <form method="POST" action="{{ route('ketua.halaqah.members.destroy', [$halaqah, $member]) }}" onsubmit="return confirm('Keluarkan dari halaqah ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-sm text-rose-700 hover:underline">Keluarkan</button>
                            </form>
                        </div>
                        @if ($targetHalaqah->isNotEmpty())
                            <form method="POST" action="{{ route('ketua.halaqah.members.mutate', [$halaqah, $member]) }}" class="grid gap-2 sm:grid-cols-4">
                                @csrf
                                <select name="target_halaqah_id" class="border-gray-300 rounded-md shadow-sm text-sm" required>
                                    <option value="">Pindah ke...</option>
                                    @foreach ($targetHalaqah as $target)
                                        <option value="{{ $target->id }}">{{ $target->name }}</option>
                                    @endforeach
                                </select>
                                <x-text-input type="date" name="moved_at" :value="now()->toDateString()" class="text-sm" required />
                                <x-text-input name="mutation_note" placeholder="Catatan mutasi" class="text-sm" />
                                <button class="text-sm text-teal-700 hover:underline">Mutasi</button>
                            </form>
                        @endif
                    </li>
                @empty
                    <li class="py-3 text-sm text-slate-500">Belum ada anggota.</li>
                @endforelse
            </ul>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-semibold">Jadwal mingguan</h2>
            <form method="POST" action="{{ route('ketua.halaqah.schedules.store', $halaqah) }}" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                @csrf
                <select name="day_of_week" class="border-gray-300 rounded-md shadow-sm" required>
                    @foreach ($days as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select name="location_id" class="border-gray-300 rounded-md shadow-sm" required>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                    @endforeach
                </select>
                <x-text-input type="time" name="start_time" required />
                <x-text-input type="time" name="end_time" required />
                <x-primary-button>Tambah slot</x-primary-button>
            </form>
            <x-input-error class="mt-2" :messages="$errors->get('start_time')" />
            <x-input-error class="mt-2" :messages="$errors->get('end_time')" />

            <ul class="mt-4 divide-y divide-slate-100">
                @forelse ($halaqah->schedules as $schedule)
                    <li class="py-3 flex items-center justify-between gap-3 text-sm">
                        <span>
                            {{ $days[$schedule->day_of_week] }}
                            {{ substr($schedule->start_time, 0, 5) }}–{{ substr($schedule->end_time, 0, 5) }}
                            · {{ $schedule->location->name }}
                        </span>
                        <form method="POST" action="{{ route('ketua.halaqah.schedules.destroy', [$halaqah, $schedule]) }}">
                            @csrf
                            @method('DELETE')
                            <button class="text-rose-700 hover:underline">Hapus</button>
                        </form>
                    </li>
                @empty
                    <li class="py-3 text-sm text-slate-500">Belum ada jadwal.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-app-layout>
