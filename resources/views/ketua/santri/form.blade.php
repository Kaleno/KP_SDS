<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">{{ isset($santri) ? 'Ubah santri' : 'Tambah santri' }}</h1>
    </x-slot>

    @php
        $santri = $santri ?? null;
        $action = $santri ? route('ketua.santri.update', $santri) : route('ketua.santri.store');
    @endphp

    <form method="POST" action="{{ $action }}" class="max-w-xl bg-white rounded-xl border border-slate-200 p-5 grid gap-4">
        @csrf
        @if ($santri)
            @method('PUT')
        @endif
        <div>
            <x-input-label for="name" value="Nama" />
            <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $santri?->user->name)" required />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>
        <div>
            <x-input-label for="nis" value="NIS (juga username login)" />
            <x-text-input id="nis" name="nis" class="mt-1 block w-full" :value="old('nis', $santri?->nis)" required />
            <x-input-error class="mt-2" :messages="$errors->get('nis')" />
        </div>
        <div>
            <x-input-label for="gender" value="Gender" />
            <select id="gender" name="gender" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                @foreach (\App\Enums\Gender::cases() as $gender)
                    <option value="{{ $gender->value }}" @selected(old('gender', $santri?->gender->value) === $gender->value)>{{ $gender->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="birth_date" value="Tanggal lahir" />
            <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1 block w-full" :value="old('birth_date', $santri?->birth_date?->format('Y-m-d'))" />
        </div>
        <div>
            <x-input-label for="status" value="Status" />
            <select id="status" name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                @foreach (\App\Enums\SantriStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $santri?->status->value ?? 'aktif') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="email" value="Email (opsional)" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $santri?->user->email)" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>
        <div>
            <x-input-label for="phone" value="Telepon" />
            <x-text-input id="phone" name="phone" class="mt-1 block w-full" :value="old('phone', $santri?->user->phone)" />
        </div>
        <div>
            <x-input-label for="password" :value="$santri ? 'Kata sandi baru (opsional)' : 'Kata sandi'" />
            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" :required="!$santri" />
            <x-input-error class="mt-2" :messages="$errors->get('password')" />
        </div>
        <div>
            <x-input-label for="password_confirmation" value="Ulangi kata sandi" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" :required="!$santri" />
        </div>
        <div>
            <x-primary-button>Simpan</x-primary-button>
        </div>
    </form>
</x-app-layout>
