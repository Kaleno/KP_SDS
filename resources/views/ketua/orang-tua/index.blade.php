<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="ui-section-title">Master data</p>
                <h1 class="font-display text-2xl font-semibold text-teal-950">Orang tua</h1>
                <p class="text-sm text-slate-500">Taut anak lewat NIS</p>
            </div>
            <a href="{{ route('ketua.orang-tua.create') }}" class="btn-primary">
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
                        <th>Anak</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($parents as $parent)
                        <tr>
                            <td data-label="Nama" class="font-medium">{{ $parent->name }}</td>
                            <td data-label="Username">{{ $parent->username }}</td>
                            <td data-label="Anak">{{ $parent->children->pluck('user.name')->join(', ') ?: '—' }}</td>
                            <td data-label="">
                                <a href="{{ route('ketua.orang-tua.edit', $parent) }}" class="ui-link">Ubah</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">Belum ada orang tua.</td></tr>
                    @endempty
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
