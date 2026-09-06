<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="ui-section-title">Master data</p>
                <h1 class="font-display text-2xl font-semibold text-teal-950">Santri</h1>
                <p class="text-sm text-slate-500">Username login = NIS</p>
            </div>
            <a href="{{ route('ketua.santri.create') }}" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" /> Tambah
            </a>
        </div>
    </x-slot>

    <div class="max-w-5xl">
        <div class="ui-table-wrap">
            <div class="overflow-x-auto">
                <table class="ui-table ui-table-stack">
                    <thead>
                        <tr>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>Gender</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($santriList as $santri)
                            <tr>
                                <td data-label="NIS" class="font-medium">{{ $santri->nis }}</td>
                                <td data-label="Nama">{{ $santri->user->name }}</td>
                                <td data-label="Gender">{{ $santri->gender->label() }}</td>
                                <td data-label="Status">
                                    <x-badge :tone="$santri->status->value === 'aktif' ? 'ok' : 'muted'">{{ $santri->status->label() }}</x-badge>
                                </td>
                                <td data-label="">
                                    <a href="{{ route('ketua.santri.edit', $santri) }}" class="ui-link">Ubah</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Belum ada santri.</td></tr>
                        @endempty
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
