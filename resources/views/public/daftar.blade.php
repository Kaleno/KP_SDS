<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6">
        <p class="ui-section-title">Pendaftaran</p>
        <h2 class="mt-1 font-display text-2xl text-teal-950">Daftar santri baru</h2>
    </div>

    <form method="POST" action="{{ route('daftar.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="name" value="Nama santri" />
            <x-text-input id="name" name="name" class="mt-1.5" :value="old('name')" required autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="parent_name" value="Nama orang tua" />
            <x-text-input id="parent_name" name="parent_name" class="mt-1.5" :value="old('parent_name')" required />
            <x-input-error class="mt-2" :messages="$errors->get('parent_name')" />
        </div>

        <div>
            <x-input-label for="school_level" value="Sekolah" />
            <select id="school_level" name="school_level" class="ui-select mt-1.5" required>
                <option value="">Pilih jenjang</option>
                @foreach ($schoolLevels as $level)
                    <option value="{{ $level->value }}" @selected(old('school_level') === $level->value)>{{ $level->label() }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('school_level')" />
        </div>

        <div>
            <x-input-label for="birth_date" value="Tanggal lahir" />
            <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1.5" :value="old('birth_date')" required />
            <x-input-error class="mt-2" :messages="$errors->get('birth_date')" />
        </div>

        <div>
            <x-input-label for="gender" value="Jenis kelamin" />
            <select id="gender" name="gender" class="ui-select mt-1.5" required>
                @foreach ($genders as $gender)
                    <option value="{{ $gender->value }}" @selected(old('gender') === $gender->value)>{{ $gender->label() }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('gender')" />
        </div>

        <div>
            <x-input-label for="track" value="Jalur mengaji" />
            <select id="track" name="track" class="ui-select mt-1.5" required>
                @foreach ($tracks as $track)
                    <option value="{{ $track->value }}" @selected(old('track', 'iqro') === $track->value)>{{ $track->label() }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('track')" />
        </div>

        <div>
            <x-input-label for="photo" value="Foto profil (opsional)" />
            <input id="photo" name="photo" type="file" accept="image/*" capture="user" class="mt-1.5 block w-full text-sm text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:font-semibold file:text-teal-800" />
            <x-input-error class="mt-2" :messages="$errors->get('photo')" />
        </div>

        <x-primary-button class="btn-block min-h-12">Kirim pendaftaran</x-primary-button>

        <p class="text-center text-sm text-slate-500">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-semibold text-teal-800">Masuk</a>
        </p>
    </form>
</x-guest-layout>
