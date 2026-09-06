<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">{{ isset($halaqah) ? 'Ubah halaqah' : 'Buat halaqah' }}</h1>
    </x-slot>

    @php
        $halaqah = $halaqah ?? null;
        $action = $halaqah ? route('ketua.halaqah.update', $halaqah) : route('ketua.halaqah.store');
    @endphp

    <form method="POST" action="{{ $action }}" class="max-w-xl bg-white rounded-xl border border-slate-200 p-5 grid gap-4">
        @csrf
        @if ($halaqah)
            @method('PUT')
        @endif
        <div>
            <x-input-label for="name" value="Nama kelompok" />
            <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $halaqah?->name)" required placeholder="Halaqah Tahfidz A" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>
        <div>
            <x-input-label for="academic_year_id" value="Tahun ajaran" />
            <select id="academic_year_id" name="academic_year_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                @foreach ($years as $year)
                    <option value="{{ $year->id }}" @selected(old('academic_year_id', $halaqah?->academic_year_id) == $year->id)>
                        {{ $year->name }}{{ $year->is_active ? ' (aktif)' : '' }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('academic_year_id')" />
        </div>
        <div>
            <x-input-label for="ustaz_user_id" value="Ustaz pembimbing" />
            <select id="ustaz_user_id" name="ustaz_user_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                @foreach ($ustazList as $ustaz)
                    <option value="{{ $ustaz->id }}" @selected(old('ustaz_user_id', $halaqah?->ustaz_user_id) == $ustaz->id)>{{ $ustaz->name }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('ustaz_user_id')" />
        </div>
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-teal-700" @checked(old('is_active', $halaqah?->is_active ?? true))>
            Aktif
        </label>
        <div>
            <x-primary-button>Simpan</x-primary-button>
        </div>
    </form>
</x-app-layout>
