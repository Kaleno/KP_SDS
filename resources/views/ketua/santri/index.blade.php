<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold text-slate-800">Santri</h1>
                <p class="text-sm text-slate-500">Username login = NIS</p>
            </div>
            <a href="{{ route('ketua.santri.create') }}" class="text-sm font-medium text-teal-700">Tambah</a>
        </div>
    </x-slot>

    <div class="max-w-5xl bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">NIS</th>
                        <th class="px-5 py-3 font-medium">Nama</th>
                        <th class="px-5 py-3 font-medium">Gender</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($santriList as $santri)
                        <tr>
                            <td class="px-5 py-3">{{ $santri->nis }}</td>
                            <td class="px-5 py-3">{{ $santri->user->name }}</td>
                            <td class="px-5 py-3">{{ $santri->gender->label() }}</td>
                            <td class="px-5 py-3">{{ $santri->status->label() }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('ketua.santri.edit', $santri) }}" class="text-teal-700 hover:underline">Ubah</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-6 text-center text-slate-500">Belum ada santri.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
