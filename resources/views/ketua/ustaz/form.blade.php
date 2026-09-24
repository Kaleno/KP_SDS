@php
    $ustaz = $ustaz ?? null;
    $action = $ustaz ? route('ketua.ustaz.update', $ustaz) : route('ketua.ustaz.store');
    $currentRole = old('teaching_role', $ustaz?->getRoleNames()->first() ?? \App\Support\Role::Pengajar);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Pengajar</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">{{ $ustaz ? 'Ubah ustadz' : 'Tambah ustadz' }}</h1>
        </div>
    </x-slot>

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="max-w-xl ui-card p-5 sm:p-6 grid gap-4">
        @csrf
        @if ($ustaz)
            @method('PUT')
        @endif

        <div>
            <x-input-label for="photo" value="Foto profil" />
            @if ($ustaz?->photoUrl())
                <img src="{{ $ustaz->photoUrl() }}" alt="" class="mt-1.5 mb-2 h-20 w-20 rounded-full object-cover">
            @endif
            <input id="photo" name="photo" type="file" accept="image/*" capture="user" class="mt-1.5 block w-full text-sm text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:font-semibold file:text-teal-800" />
            <p class="mt-1 text-xs text-slate-500">JPG/PNG/WebP, maks. 2 MB.</p>
            <x-input-error class="mt-2" :messages="$errors->get('photo')" />
        </div>

        <div>
            <x-input-label for="teaching_role" value="Jenis akun" />
            <select id="teaching_role" name="teaching_role" class="ui-select mt-1.5" required>
                <option value="{{ \App\Support\Role::KetuaPengajar }}" @selected($currentRole === \App\Support\Role::KetuaPengajar)>Ketua Pengajar</option>
                <option value="{{ \App\Support\Role::Pengajar }}" @selected($currentRole === \App\Support\Role::Pengajar)>Pengajar</option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('teaching_role')" />
        </div>

        <div>
            <x-input-label for="name" value="Nama" />
            <x-text-input id="name" name="name" class="mt-1.5" :value="old('name', $ustaz?->name)" required />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="nip" value="Nomor induk pengajar (NIP)" />
            <x-text-input id="nip" name="nip" class="mt-1.5" :value="old('nip', $ustaz?->nip)" required />
            <x-input-error class="mt-2" :messages="$errors->get('nip')" />
        </div>

        <div>
            <x-input-label for="birth_date" value="Tanggal lahir" />
            <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1.5" :value="old('birth_date', $ustaz?->birth_date?->format('Y-m-d'))" />
        </div>

        <div>
            <x-input-label for="address" value="Alamat" />
            <textarea id="address" name="address" rows="3" class="ui-input mt-1.5">{{ old('address', $ustaz?->address) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('address')" />
        </div>

        <div>
            <x-input-label for="phone" value="Nomor telepon" />
            <x-text-input id="phone" name="phone" class="mt-1.5" :value="old('phone', $ustaz?->phone)" />
        </div>

        <div>
            <x-input-label for="education_level" value="Jenjang pendidikan" />
            <select id="education_level" name="education_level" class="ui-select mt-1.5">
                <option value="">—</option>
                @foreach ($educationLevels as $level)
                    <option value="{{ $level->value }}" @selected(old('education_level', $ustaz?->education_level?->value) === $level->value)>{{ $level->label() }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('education_level')" />
        </div>

        <div>
            <x-input-label for="is_active" value="Status pengajar" />
            <select id="is_active" name="is_active" class="ui-select mt-1.5" required>
                <option value="1" @selected((string) old('is_active', $ustaz?->is_active ?? true) === '1')>Aktif</option>
                <option value="0" @selected((string) old('is_active', $ustaz?->is_active ?? true) === '0')>Tidak aktif</option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
        </div>

        <div>
            <x-input-label for="username" value="Username" />
            <x-text-input id="username" name="username" class="mt-1.5" :value="old('username', $ustaz?->username)" required />
            <x-input-error class="mt-2" :messages="$errors->get('username')" />
        </div>

        <div>
            <x-input-label for="email" value="Email (opsional)" />
            <x-text-input id="email" name="email" type="email" class="mt-1.5" :value="old('email', $ustaz?->email)" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" :value="$ustaz ? 'Kata sandi baru (opsional)' : 'Kata sandi'" />
            <x-text-input id="password" name="password" type="password" class="mt-1.5" :required="!$ustaz" />
            <x-input-error class="mt-2" :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Ulangi kata sandi" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1.5" :required="!$ustaz" />
        </div>

        <div>
            <x-primary-button>Simpan</x-primary-button>
        </div>
    </form>
</x-app-layout>
