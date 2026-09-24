<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Daftar Akun</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Tambah ketua</h1>
        </div>
    </x-slot>

    <div class="ui-page">
        <x-card>
            <form method="POST" action="{{ route('super-admin.ketua.store') }}" class="grid gap-4 sm:grid-cols-2">
                @csrf
                <div class="sm:col-span-2">
                    <x-input-label for="name" value="Nama" />
                    <x-text-input id="name" name="name" class="mt-1.5" :value="old('name')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="place_name" value="Nama tempat" />
                    <x-text-input id="place_name" name="place_name" class="mt-1.5" :value="old('place_name')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('place_name')" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="address" value="Alamat" />
                    <textarea id="address" name="address" rows="3" class="ui-input mt-1.5" required>{{ old('address') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('address')" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="phone" value="Telepon" />
                    <x-text-input id="phone" name="phone" class="mt-1.5" :value="old('phone')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('phone')" />
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
                <div class="sm:col-span-2 flex flex-wrap items-center gap-3">
                    <x-primary-button>Simpan akun</x-primary-button>
                    <a href="{{ route('super-admin.ketua.index') }}" class="ui-link text-sm">Batal</a>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
