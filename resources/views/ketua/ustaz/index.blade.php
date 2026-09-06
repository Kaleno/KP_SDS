<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold text-slate-800">Pengajar</h1>
                <p class="text-sm text-slate-500">Akun ustaz pembimbing halaqah</p>
            </div>
            <a href="{{ route('ketua.ustaz.create') }}" class="text-sm font-medium text-teal-700">Tambah</a>
        </div>
    </x-slot>

    <div class="max-w-4xl bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-5 py-3 font-medium">Nama</th>
                    <th class="px-5 py-3 font-medium">Username</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($ustazList as $ustaz)
                    <tr>
                        <td class="px-5 py-3">{{ $ustaz->name }}</td>
                        <td class="px-5 py-3">{{ $ustaz->username }}</td>
                        <td class="px-5 py-3">{{ $ustaz->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                        <td class="px-5 py-3 text-right space-x-3">
                            <a href="{{ route('ketua.ustaz.edit', $ustaz) }}" class="text-teal-700 hover:underline">Ubah</a>
                            <form method="POST" action="{{ route('ketua.ustaz.toggle', $ustaz) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button class="text-slate-600 hover:underline">{{ $ustaz->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-6 text-center text-slate-500">Belum ada ustaz.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
