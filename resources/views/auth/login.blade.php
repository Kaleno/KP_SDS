<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6 hidden lg:block">
        <p class="ui-section-title">Masuk akun</p>
        <h2 class="mt-1 font-display text-2xl text-teal-950">Selamat datang kembali</h2>
        <p class="mt-1 text-sm text-slate-500">Gunakan username atau email yang diberikan Ketua.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="login" value="Username atau email" />
            <x-text-input id="login" class="mt-1.5" type="text" name="login" :value="old('login')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('login')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Kata sandi" />
            <x-text-input id="password" class="mt-1.5" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label for="remember_me" class="inline-flex items-center">
            <input id="remember_me" type="checkbox" class="rounded-md border-slate-300 text-teal-800 shadow-none focus:ring-teal-700/20" name="remember">
            <span class="ms-2 text-sm text-slate-600">Ingat saya</span>
        </label>

        <x-primary-button class="btn-block min-h-12">
            Masuk
        </x-primary-button>
    </form>
</x-guest-layout>
