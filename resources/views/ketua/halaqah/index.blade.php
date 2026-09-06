<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="ui-section-title">Master data</p>
                <h1 class="font-display text-2xl font-semibold text-teal-950">Halaqah</h1>
                <p class="text-sm text-slate-500">Kelompok, pembimbing, anggota, jadwal</p>
            </div>
            <a href="{{ route('ketua.halaqah.create') }}" class="btn-primary">
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
                            <th>Nama</th>
                            <th>Tahun</th>
                            <th>Ustaz</th>
                            <th>Anggota</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($halaqahList as $item)
                            <tr>
                                <td data-label="Nama" class="font-medium">{{ $item->name }}</td>
                                <td data-label="Tahun">{{ $item->academicYear->name }}</td>
                                <td data-label="Ustaz">{{ $item->ustaz->name }}</td>
                                <td data-label="Anggota">{{ $item->active_members_count }}</td>
                                <td data-label="">
                                    <a href="{{ route('ketua.halaqah.show', $item) }}" class="ui-link">Kelola</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Belum ada halaqah.</td></tr>
                        @endempty
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
