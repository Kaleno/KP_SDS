<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Pengajar</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Ubah ustaz</h1>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('ketua.ustaz.update', $ustaz) }}" class="max-w-xl ui-card p-5 sm:p-6 grid gap-4">
        @csrf
        @method('PUT')
        <div>
            <x-input-label for="name" value="Nama" />
            <x-text-input id="name" name="name" class="mt-1.5" :value="old('name', $ustaz->name)" required />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>
        <div>
            <x-input-label for="username" value="Username" />
            <x-text-input id="username" name="username" class="mt-1.5" :value="old('username', $ustaz->username)" required />
            <x-input-error class="mt-2" :messages="$errors->get('username')" />
        </div>
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="mt-1.5" :value="old('email', $ustaz->email)" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>
        <div>
            <x-input-label for="phone" value="Telepon" />
            <x-text-input id="phone" name="phone" class="mt-1.5" :value="old('phone', $ustaz->phone)" />
        </div>
        <div>
            <x-input-label for="password" value="Kata sandi baru (opsional)" />
            <x-text-input id="password" name="password" type="password" class="mt-1.5" />
            <x-input-error class="mt-2" :messages="$errors->get('password')" />
        </div>
        <div>
            <x-input-label for="password_confirmation" value="Ulangi kata sandi" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1.5" />
        </div>
        <div>
            <x-primary-button>Simpan</x-primary-button>
        </div>
    </form>
</x-app-layout>
