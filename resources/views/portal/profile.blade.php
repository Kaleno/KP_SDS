<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Santri</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Data diri</h1>
            <p class="text-sm text-slate-500">NIS {{ $santri->nis }} · {{ $santri->track?->label() }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('portal.profile.update') }}" enctype="multipart/form-data" class="max-w-xl ui-card p-5 grid gap-4">
        @csrf
        @method('PUT')

        <div class="flex flex-col items-center gap-3">
            @if ($santri->photoUrl())
                <img src="{{ $santri->photoUrl() }}" alt="" class="h-24 w-24 rounded-3xl object-cover">
            @else
                <div class="flex h-24 w-24 items-center justify-center rounded-3xl bg-teal-50 text-2xl font-semibold text-teal-800">
                    {{ mb_substr($santri->user->name, 0, 1) }}
                </div>
            @endif
            <input id="photo" name="photo" type="file" accept="image/*" capture="user" class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:font-semibold file:text-teal-800" />
            <x-input-error :messages="$errors->get('photo')" />
        </div>

        <div>
            <x-input-label for="name" value="Nama" />
            <x-text-input id="name" name="name" class="mt-1.5" :value="old('name', $santri->user->name)" required />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>
        <div>
            <x-input-label for="parent_name" value="Nama orang tua" />
            <x-text-input id="parent_name" name="parent_name" class="mt-1.5" :value="old('parent_name', $santri->parent_name)" />
        </div>
        <div>
            <x-input-label for="school_level" value="Sekolah" />
            <select id="school_level" name="school_level" class="ui-select mt-1.5">
                <option value="">—</option>
                @foreach ($schoolLevels as $level)
                    <option value="{{ $level->value }}" @selected(old('school_level', $santri->school_level?->value) === $level->value)>{{ $level->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="birth_date" value="Tanggal lahir" />
            <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1.5" :value="old('birth_date', $santri->birth_date?->format('Y-m-d'))" />
        </div>

        <x-primary-button class="btn-block min-h-12">Simpan</x-primary-button>
    </form>
</x-app-layout>
