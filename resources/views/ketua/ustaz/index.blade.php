<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="ui-section-title">Master data</p>
                <h1 class="font-display text-2xl font-semibold text-teal-950">Pengajar</h1>
                <p class="text-sm text-slate-500">Akun ustaz pembimbing halaqah</p>
            </div>
            <a href="{{ route('ketua.ustaz.create') }}" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" /> Tambah
            </a>
        </div>
    </x-slot>

    <div class="max-w-4xl">
        <div class="ui-table-wrap">
            <table class="ui-table ui-table-stack">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ustazList as $ustaz)
                        <tr>
                            <td data-label="Nama" class="font-medium">{{ $ustaz->name }}</td>
                            <td data-label="Username">{{ $ustaz->username }}</td>
                            <td data-label="Status">
                                <x-badge :tone="$ustaz->is_active ? 'ok' : 'muted'">{{ $ustaz->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                            </td>
                            <td data-label="">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('ketua.ustaz.edit', $ustaz) }}" class="ui-link">Ubah</a>
                                    <form method="POST" action="{{ route('ketua.ustaz.toggle', $ustaz) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="text-sm font-semibold text-slate-500 hover:text-teal-800">{{ $ustaz->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">Belum ada ustaz.</td></tr>
                    @endempty
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
