<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Sistem</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Akun Ketua</h1>
            <p class="text-sm text-slate-500">Buat dan aktifkan akun pimpinan operasional</p>
        </div>
    </x-slot>

    <div class="max-w-4xl space-y-6">
        <x-card>
            <h2 class="font-display text-lg font-semibold text-teal-950">Tambah Ketua</h2>
            <form method="POST" action="{{ route('super-admin.ketua.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                @csrf
                <div class="sm:col-span-2">
                    <x-input-label for="name" value="Nama" />
                    <x-text-input id="name" name="name" class="mt-1.5" :value="old('name')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="username" value="Username" />
                    <x-text-input id="username" name="username" class="mt-1.5" :value="old('username')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('username')" />
                </div>
                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1.5" :value="old('email')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>
                <div>
                    <x-input-label for="password" value="Kata sandi" />
                    <x-text-input id="password" name="password" type="password" class="mt-1.5" required />
                    <x-input-error class="mt-2" :messages="$errors->get('password')" />
                </div>
                <div>
                    <x-input-label for="password_confirmation" value="Ulangi kata sandi" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1.5" required />
                </div>
                <div class="sm:col-span-2">
                    <x-primary-button>Simpan akun</x-primary-button>
                </div>
            </form>
        </x-card>

        <div class="ui-table-wrap">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-display text-lg font-semibold text-teal-950">Daftar Ketua</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="ui-table ui-table-stack">
                    <thead>
                        <tr>
                            <th>Nama</th>
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
                                <td data-label="Username">{{ $ketua->username }}</td>
                                <td data-label="Email">{{ $ketua->email }}</td>
                                <td data-label="Status">
                                    @if ($ketua->is_active)
                                        <x-badge>Aktif</x-badge>
                                    @else
                                        <x-badge tone="danger">Nonaktif</x-badge>
                                    @endif
                                </td>
                                <td data-label="">
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
                                <td colspan="5" class="px-5 py-8 text-center text-slate-500">Belum ada akun Ketua.</td>
                            </tr>
                        @endempty
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
