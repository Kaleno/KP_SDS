<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Akun Ketua</h1>
        <p class="text-sm text-slate-500">Buat dan aktifkan akun pimpinan operasional</p>
    </x-slot>

    <div class="max-w-4xl space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-800">Tambah Ketua</h2>
            <form method="POST" action="{{ route('super-admin.ketua.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                @csrf
                <div class="sm:col-span-2">
                    <x-input-label for="name" value="Nama" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="username" value="Username" />
                    <x-text-input id="username" name="username" class="mt-1 block w-full" :value="old('username')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('username')" />
                </div>
                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>
                <div>
                    <x-input-label for="password" value="Kata sandi" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required />
                    <x-input-error class="mt-2" :messages="$errors->get('password')" />
                </div>
                <div>
                    <x-input-label for="password_confirmation" value="Ulangi kata sandi" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required />
                </div>
                <div class="sm:col-span-2">
                    <x-primary-button>Simpan akun</x-primary-button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-200">
                <h2 class="font-semibold text-slate-800">Daftar Ketua</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-medium">Nama</th>
                            <th class="px-5 py-3 font-medium">Username</th>
                            <th class="px-5 py-3 font-medium">Email</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($ketuaAccounts as $ketua)
                            <tr>
                                <td class="px-5 py-3 text-slate-800">{{ $ketua->name }}</td>
                                <td class="px-5 py-3">{{ $ketua->username }}</td>
                                <td class="px-5 py-3">{{ $ketua->email }}</td>
                                <td class="px-5 py-3">
                                    @if ($ketua->is_active)
                                        <span class="text-teal-700 font-medium">Aktif</span>
                                    @else
                                        <span class="text-rose-700 font-medium">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <form method="POST" action="{{ route('super-admin.ketua.toggle', $ketua) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-sm text-teal-700 hover:underline">
                                            {{ $ketua->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-6 text-center text-slate-500">Belum ada akun Ketua.</td>
                            </tr>
                        @endempty
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
