<x-guest-layout>
    <p class="mb-4 text-sm text-slate-600">
        Area aman. Konfirmasikan kata sandi sebelum melanjutkan.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="password" value="Kata sandi" />
            <x-text-input id="password" class="mt-1.5" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-primary-button class="btn-block">
            Konfirmasi
        </x-primary-button>
    </form>
</x-guest-layout>
