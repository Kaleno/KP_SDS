<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Lokasi</h1>
        <p class="text-sm text-slate-500">Tempat sesi mengaji</p>
    </x-slot>

    <div class="max-w-4xl space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-semibold">Tambah lokasi</h2>
            <form method="POST" action="{{ route('ketua.locations.store') }}" class="mt-4 grid gap-4">
                @csrf
                <div>
                    <x-input-label for="name" value="Nama" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="description" value="Keterangan" />
                    <x-text-input id="description" name="description" class="mt-1 block w-full" :value="old('description')" />
                </div>
                <div>
                    <x-primary-button>Simpan</x-primary-button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Nama</th>
                        <th class="px-5 py-3 font-medium">Keterangan</th>
                        <th class="px-5 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($locations as $location)
                        <tr>
                            <td class="px-5 py-3">{{ $location->name }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $location->description }}</td>
                            <td class="px-5 py-3 text-right">
                                <form method="POST" action="{{ route('ketua.locations.destroy', $location) }}" onsubmit="return confirm('Hapus lokasi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-rose-700 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-6 text-center text-slate-500">Belum ada lokasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
