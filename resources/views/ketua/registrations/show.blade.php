<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Pendaftaran</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">{{ $registration->name }}</h1>
            <p class="text-sm text-slate-500">{{ $registration->status->label() }}</p>
        </div>
    </x-slot>

    <div class="max-w-xl space-y-5">
        <div class="ui-card p-5 space-y-4">
            @if ($registration->photoUrl())
                <img src="{{ $registration->photoUrl() }}" alt="" class="mx-auto h-32 w-32 rounded-3xl object-cover">
            @endif
            <dl class="grid gap-3 text-sm">
                <div class="flex justify-between gap-3"><dt class="text-slate-500">Orang tua</dt><dd class="font-medium text-teal-950">{{ $registration->parent_name }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500">Sekolah</dt><dd class="font-medium text-teal-950">{{ $registration->school_level->label() }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500">Lahir</dt><dd class="font-medium text-teal-950">{{ $registration->birth_date->format('d/m/Y') }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500">Gender</dt><dd class="font-medium text-teal-950">{{ $registration->gender->label() }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500">Jalur</dt><dd class="font-medium text-teal-950">{{ $registration->track->label() }}</dd></div>
            </dl>
        </div>

        @if ($registration->status === \App\Enums\RegistrationStatus::Pending)
            <form method="POST" action="{{ route('ketua.registrations.approve', $registration) }}" class="ui-card p-5 grid gap-4">
                @csrf
                <p class="font-semibold text-teal-950">Setujui & buat akun</p>
                <div>
                    <x-input-label for="username" value="Username login" />
                    <x-text-input id="username" name="username" class="mt-1.5" :value="old('username')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('username')" />
                </div>
                <div>
                    <x-input-label for="nis" value="NIS" />
                    <x-text-input id="nis" name="nis" class="mt-1.5" :value="old('nis')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('nis')" />
                </div>
                <div>
                    <x-input-label for="password" value="Kata sandi awal" />
                    <x-text-input id="password" name="password" type="password" class="mt-1.5" required />
                    <x-input-error class="mt-2" :messages="$errors->get('password')" />
                </div>
                <div>
                    <x-input-label for="password_confirmation" value="Ulangi kata sandi" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1.5" required />
                </div>
                <x-primary-button class="btn-block min-h-12">Setujui</x-primary-button>
            </form>

            <form method="POST" action="{{ route('ketua.registrations.reject', $registration) }}" class="ui-card p-5 grid gap-4">
                @csrf
                <p class="font-semibold text-rose-800">Tolak pendaftaran</p>
                <div>
                    <x-input-label for="rejection_note" value="Alasan (opsional)" />
                    <x-text-input id="rejection_note" name="rejection_note" class="mt-1.5" :value="old('rejection_note')" />
                </div>
                <button type="submit" class="btn-secondary btn-block min-h-12 text-rose-800">Tolak</button>
            </form>
        @endif
    </div>
</x-app-layout>
