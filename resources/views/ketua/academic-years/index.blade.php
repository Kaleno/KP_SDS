<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Tahun ajaran</h1>
        <p class="text-sm text-slate-500">Hanya satu tahun yang aktif</p>
    </x-slot>

    <div class="max-w-4xl space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-semibold">Tambah tahun ajaran</h2>
            <form method="POST" action="{{ route('ketua.academic-years.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                @csrf
                <div class="sm:col-span-2">
                    <x-input-label for="name" value="Nama" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name')" required placeholder="2026/2027" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="start_date" value="Mulai" />
                    <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
                </div>
                <div>
                    <x-input-label for="end_date" value="Selesai" />
                    <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" :value="old('end_date')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
                </div>
                <label class="sm:col-span-2 inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-teal-700">
                    Jadikan tahun aktif
                </label>
                <div class="sm:col-span-2">
                    <x-primary-button>Simpan</x-primary-button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Nama</th>
                        <th class="px-5 py-3 font-medium">Periode</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($years as $year)
                        <tr>
                            <td class="px-5 py-3">{{ $year->name }}</td>
                            <td class="px-5 py-3">{{ $year->start_date->format('d/m/Y') }} – {{ $year->end_date->format('d/m/Y') }}</td>
                            <td class="px-5 py-3">
                                @if ($year->is_active)
                                    <span class="text-teal-700 font-medium">Aktif</span>
                                @else
                                    <span class="text-slate-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right space-x-3">
                                @unless ($year->is_active)
                                    <form method="POST" action="{{ route('ketua.academic-years.activate', $year) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="text-teal-700 hover:underline">Aktifkan</button>
                                    </form>
                                @endunless
                                <form method="POST" action="{{ route('ketua.academic-years.destroy', $year) }}" class="inline" onsubmit="return confirm('Hapus tahun ajaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-rose-700 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-6 text-center text-slate-500">Belum ada tahun ajaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
