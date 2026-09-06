<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Master data</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Tahun ajaran</h1>
            <p class="text-sm text-slate-500">Hanya satu tahun yang aktif</p>
        </div>
    </x-slot>

    <div class="max-w-4xl space-y-6">
        <x-card>
            <h2 class="font-display text-lg font-semibold text-teal-950">Tambah tahun ajaran</h2>
            <form method="POST" action="{{ route('ketua.academic-years.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                @csrf
                <div class="sm:col-span-2">
                    <x-input-label for="name" value="Nama" />
                    <x-text-input id="name" name="name" class="mt-1.5" :value="old('name')" required placeholder="2026/2027" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="start_date" value="Mulai" />
                    <x-text-input id="start_date" name="start_date" type="date" class="mt-1.5" :value="old('start_date')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
                </div>
                <div>
                    <x-input-label for="end_date" value="Selesai" />
                    <x-text-input id="end_date" name="end_date" type="date" class="mt-1.5" :value="old('end_date')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
                </div>
                <label class="sm:col-span-2 inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" class="rounded-md border-slate-300 text-teal-800 focus:ring-teal-700/20">
                    Jadikan tahun aktif
                </label>
                <div class="sm:col-span-2">
                    <x-primary-button>Simpan</x-primary-button>
                </div>
            </form>
        </x-card>

        <div class="ui-table-wrap">
            <table class="ui-table ui-table-stack">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($years as $year)
                        <tr>
                            <td data-label="Nama" class="font-medium">{{ $year->name }}</td>
                            <td data-label="Periode">{{ $year->start_date->format('d/m/Y') }} – {{ $year->end_date->format('d/m/Y') }}</td>
                            <td data-label="Status">
                                @if ($year->is_active)
                                    <x-badge>Aktif</x-badge>
                                @else
                                    <x-badge tone="muted">Nonaktif</x-badge>
                                @endif
                            </td>
                            <td data-label="">
                                <div class="flex justify-end gap-3">
                                    @unless ($year->is_active)
                                        <form method="POST" action="{{ route('ketua.academic-years.activate', $year) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="ui-link">Aktifkan</button>
                                        </form>
                                    @endunless
                                    <form method="POST" action="{{ route('ketua.academic-years.destroy', $year) }}" onsubmit="return confirm('Hapus tahun ajaran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-danger-ghost min-h-10 text-sm">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">Belum ada tahun ajaran.</td></tr>
                    @endempty
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
