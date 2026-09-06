<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold text-slate-800">Orang tua</h1>
                <p class="text-sm text-slate-500">Taut anak lewat NIS</p>
            </div>
            <a href="{{ route('ketua.orang-tua.create') }}" class="text-sm font-medium text-teal-700">Tambah</a>
        </div>
    </x-slot>

    <div class="max-w-4xl bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-5 py-3 font-medium">Nama</th>
                    <th class="px-5 py-3 font-medium">Username</th>
                    <th class="px-5 py-3 font-medium">Anak</th>
                    <th class="px-5 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($parents as $parent)
                    <tr>
                        <td class="px-5 py-3">{{ $parent->name }}</td>
                        <td class="px-5 py-3">{{ $parent->username }}</td>
                        <td class="px-5 py-3">{{ $parent->children->pluck('user.name')->join(', ') ?: '—' }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('ketua.orang-tua.edit', $parent) }}" class="text-teal-700 hover:underline">Ubah</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-6 text-center text-slate-500">Belum ada orang tua.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
