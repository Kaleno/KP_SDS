<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Santri</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">{{ isset($santri) ? 'Ubah santri' : 'Tambah santri' }}</h1>
        </div>
    </x-slot>

    @php
        $santri = $santri ?? null;
        $action = $santri ? route('ketua.santri.update', $santri) : route('ketua.santri.store');
    @endphp

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="max-w-xl ui-card p-5 sm:p-6 grid gap-4">
        @csrf
        @if ($santri)
            @method('PUT')
        @endif

        <div>
            <x-input-label for="photo" value="Foto profil" />
            @if ($santri?->photoUrl())
                <img src="{{ $santri->photoUrl() }}" alt="" class="mt-1.5 mb-2 h-20 w-20 rounded-full object-cover">
            @endif
            <input id="photo" name="photo" type="file" accept="image/*" capture="user" class="mt-1.5 block w-full text-sm text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:font-semibold file:text-teal-800" />
            <p class="mt-1 text-xs text-slate-500">JPG/PNG/WebP, maks. 2 MB.</p>
            <x-input-error class="mt-2" :messages="$errors->get('photo')" />
        </div>

        <div>
            <x-input-label for="name" value="Nama" />
            <x-text-input id="name" name="name" class="mt-1.5" :value="old('name', $santri?->user->name)" required />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>
        <div>
            <x-input-label for="nis" value="NIS (juga username login)" />
            <x-text-input id="nis" name="nis" class="mt-1.5" :value="old('nis', $santri?->nis)" required />
            <x-input-error class="mt-2" :messages="$errors->get('nis')" />
        </div>
        <div>
            <x-input-label for="gender" value="Gender" />
            <select id="gender" name="gender" class="mt-1.5 ui-input">
                @foreach (\App\Enums\Gender::cases() as $gender)
                    <option value="{{ $gender->value }}" @selected(old('gender', $santri?->gender->value) === $gender->value)>{{ $gender->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="birth_date" value="Tanggal lahir" />
            <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1.5" :value="old('birth_date', $santri?->birth_date?->format('Y-m-d'))" />
        </div>
        <div>
            <x-input-label for="parent_name" value="Nama orang tua / wali" />
            <x-text-input id="parent_name" name="parent_name" class="mt-1.5" :value="old('parent_name', $santri?->parent_name)" />
            <x-input-error class="mt-2" :messages="$errors->get('parent_name')" />
        </div>
        <div>
            <x-input-label for="address" value="Alamat" />
            <textarea id="address" name="address" rows="3" class="ui-input mt-1.5">{{ old('address', $santri?->address) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('address')" />
        </div>
        <div>
            <x-input-label for="school_level" value="Sekolah" />
            <select id="school_level" name="school_level" class="mt-1.5 ui-input">
                <option value="">—</option>
                @foreach (\App\Enums\SchoolLevel::ordered() as $level)
                    <option value="{{ $level->value }}" @selected(old('school_level', $santri?->school_level?->value) === $level->value)>{{ $level->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="track" value="Jalur mengaji" />
            <select id="track" name="track" class="mt-1.5 ui-input">
                @foreach (\App\Enums\SantriTrack::cases() as $track)
                    <option value="{{ $track->value }}" @selected(old('track', $santri?->track?->value ?? 'alquran') === $track->value)>{{ $track->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="status" value="Status" />
            <select id="status" name="status" class="mt-1.5 ui-input">
                @foreach (\App\Enums\SantriStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $santri?->status->value ?? 'aktif') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">Cuti & keluar tidak bisa login. Lulus tetap bisa masuk portal.</p>
        </div>
        <div>
            <x-input-label for="email" value="Email (opsional)" />
            <x-text-input id="email" name="email" type="email" class="mt-1.5" :value="old('email', $santri?->user->email)" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>
        <div>
            <x-input-label for="phone" value="Telepon" />
            <x-text-input id="phone" name="phone" class="mt-1.5" :value="old('phone', $santri?->user->phone)" />
        </div>
        <div>
            <x-input-label for="password" :value="$santri ? 'Kata sandi baru (opsional)' : 'Kata sandi'" />
            <x-text-input id="password" name="password" type="password" class="mt-1.5" :required="!$santri" />
            <x-input-error class="mt-2" :messages="$errors->get('password')" />
        </div>
        <div>
            <x-input-label for="password_confirmation" value="Ulangi kata sandi" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1.5" :required="!$santri" />
        </div>
        <div>
            <x-primary-button>Simpan</x-primary-button>
        </div>
    </form>
</x-app-layout>
