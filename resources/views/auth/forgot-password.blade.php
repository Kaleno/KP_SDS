<x-guest-layout>
    <p class="mb-4 text-sm text-slate-600">
        Masukkan email akun Anda. Kami akan mengirim tautan untuk mengatur ulang kata sandi.
    </p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1.5" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="btn-block">
            Kirim tautan reset
        </x-primary-button>
    </form>
</x-guest-layout>
