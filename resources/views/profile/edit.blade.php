<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Akun</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Profil</h1>
        </div>
    </x-slot>

    <div class="max-w-2xl space-y-5">
        <x-card>
            @include('profile.partials.update-profile-information-form')
        </x-card>

        <x-card>
            @include('profile.partials.update-password-form')
        </x-card>

        @unless (Auth::user()->hasAnyRole([\App\Support\Role::Santri, \App\Support\Role::OrangTua, \App\Support\Role::Ustaz]))
        <x-card>
            @include('profile.partials.delete-user-form')
        </x-card>
        @endunless
    </div>
</x-app-layout>
