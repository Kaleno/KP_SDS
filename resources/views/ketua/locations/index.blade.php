<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Master data</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Lokasi</h1>
            <p class="text-sm text-slate-500">Tempat sesi mengaji</p>
        </div>
    </x-slot>

    <div class="max-w-4xl space-y-6">
        <x-card>
            <h2 class="font-display text-lg font-semibold text-teal-950">Tambah lokasi</h2>
            <form method="POST" action="{{ route('ketua.locations.store') }}" class="mt-4 grid gap-4">
                @csrf
                <div>
                    <x-input-label for="name" value="Nama" />
                    <x-text-input id="name" name="name" class="mt-1.5" :value="old('name')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="description" value="Keterangan" />
                    <x-text-input id="description" name="description" class="mt-1.5" :value="old('description')" />
                </div>
                <div>
                    <x-primary-button>Simpan</x-primary-button>
                </div>
            </form>
        </x-card>

        <div class="ui-table-wrap">
            <table class="ui-table ui-table-stack">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Keterangan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($locations as $location)
                        <tr>
                            <td data-label="Nama" class="font-medium">{{ $location->name }}</td>
                            <td data-label="Keterangan" class="text-slate-500">{{ $location->description }}</td>
                            <td data-label="">
                                <form method="POST" action="{{ route('ketua.locations.destroy', $location) }}" onsubmit="return confirm('Hapus lokasi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-danger-ghost min-h-10 text-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-8 text-center text-slate-500">Belum ada lokasi.</td></tr>
                    @endempty
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
