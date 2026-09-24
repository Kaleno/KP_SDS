<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="ui-section-title">Sistem</p>
                <h1 class="font-display text-2xl font-semibold text-teal-950">Daftar Akun</h1>
            </div>
            <a href="{{ route('super-admin.ketua.create') }}" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" /> Tambah ketua
            </a>
        </div>
    </x-slot>

    <div class="ui-page !space-y-6">
        <div class="ui-table-wrap">
            <div class="overflow-x-auto">
                <table class="ui-table ui-table-stack">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Nama tempat</th>
                            <th>Alamat</th>
                            <th>Telepon</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ketuaAccounts as $ketua)
                            <tr>
                                <td data-label="Nama" class="font-medium text-slate-800">{{ $ketua->name }}</td>
                                <td data-label="Nama tempat">{{ $ketua->place_name ?: '—' }}</td>
                                <td data-label="Alamat">{{ $ketua->address ?: '—' }}</td>
                                <td data-label="Telepon">{{ $ketua->phone ?: '—' }}</td>
                                <td data-label="Username">{{ $ketua->username }}</td>
                                <td data-label="Email">{{ $ketua->email }}</td>
                                <td data-label="Status">
                                    @if ($ketua->is_active)
                                        <x-badge>Aktif</x-badge>
                                    @else
                                        <x-badge tone="danger">Nonaktif</x-badge>
                                    @endif
                                </td>
                                <td data-label="" class="ui-table-actions">
                                    <form method="POST" action="{{ route('super-admin.ketua.toggle', $ketua) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="ui-link text-sm">
                                            {{ $ketua->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-8 text-center text-slate-500">Belum ada akun ketua.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
