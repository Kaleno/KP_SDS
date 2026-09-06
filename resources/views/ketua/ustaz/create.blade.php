<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Pengajar</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Tambah ustaz</h1>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('ketua.ustaz.store') }}" class="max-w-xl ui-card p-5 sm:p-6 grid gap-4">
        @csrf
        <div>
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
            <x-text-input id="email" name="email" type="email" class="mt-1.5" :value="old('email')" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>
        <div>
            <x-input-label for="phone" value="Telepon" />
            <x-text-input id="phone" name="phone" class="mt-1.5" :value="old('phone')" />
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
        <div>
            <x-primary-button>Simpan</x-primary-button>
        </div>
    </form>
</x-app-layout>
