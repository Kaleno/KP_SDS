<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="ui-section-title">Pembelajaran</p>
                <h1 class="font-display text-2xl font-semibold text-teal-950">{{ isset($halaqah) ? 'Ubah kelas' : 'Buat kelas' }}</h1>
                <p class="text-sm text-slate-500">Pilih hari, jam, dan pengajar. Semua santri aktif ikut otomatis.</p>
            </div>
            <a href="{{ route('ketua.halaqah.index') }}" class="btn-secondary">Kembali</a>
        </div>
    </x-slot>

    @php
        $halaqah = $halaqah ?? null;
        $action = $halaqah ? route('ketua.halaqah.update', $halaqah) : route('ketua.halaqah.store');
        $selectedDays = old('days', $selectedDays ?? []);
        $selectedDays = array_map('intval', is_array($selectedDays) ? $selectedDays : []);
    @endphp

    @if ($ustazList->isEmpty())
        <div class="max-w-xl mb-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Belum ada pengajar. <a href="{{ route('ketua.ustaz.create') }}" class="ui-link">Tambah pengajar</a> dulu, lalu buat kelas.
        </div>
    @endif

    <form
        method="POST"
        action="{{ $action }}"
        class="max-w-xl ui-card p-5 sm:p-6 grid gap-5"
        x-data="{ days: {{ \Illuminate\Support\Js::from(array_map('strval', $selectedDays)) }} }"
    >
        @csrf
        @if ($halaqah)
            @method('PUT')
        @endif

        <div>
            <x-input-label for="name" value="Nama kelas" />
            <x-text-input id="name" name="name" class="mt-1.5" :value="old('name', $halaqah?->name)" required placeholder="Tahfidz pagi" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="ustaz_user_id" value="Pengajar" />
            <select id="ustaz_user_id" name="ustaz_user_id" class="mt-1.5 ui-input" required @disabled($ustazList->isEmpty())>
                <option value="">Pilih pengajar</option>
                @foreach ($ustazList as $ustaz)
                    <option value="{{ $ustaz->id }}" @selected(old('ustaz_user_id', $halaqah?->ustaz_user_id) == $ustaz->id)>{{ $ustaz->name }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('ustaz_user_id')" />
        </div>

        <div>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <x-input-label value="Hari pembelajaran" />
                <div class="flex gap-2">
                    <button type="button" class="btn-ghost min-h-10 px-3 text-xs" @click="days = ['1','2','3','4','5']">Senin–Jumat</button>
                    <button type="button" class="btn-ghost min-h-10 px-3 text-xs" @click="days = ['1','2','3','4','5','6','7']">Setiap hari</button>
                </div>
            </div>
            <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-4">
                @foreach ($days as $value => $label)
                    <label class="block">
                        <input type="checkbox" name="days[]" value="{{ $value }}" class="peer sr-only" x-model="days">
                        <span class="ui-choice text-sm">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('days')" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="start_time" value="Jam mulai" />
                <x-text-input id="start_time" name="start_time" type="time" class="mt-1.5" :value="old('start_time', $startTime)" required />
                <x-input-error class="mt-2" :messages="$errors->get('start_time')" />
            </div>
            <div>
                <x-input-label for="end_time" value="Jam selesai" />
                <x-text-input id="end_time" name="end_time" type="time" class="mt-1.5" :value="old('end_time', $endTime)" required />
                <x-input-error class="mt-2" :messages="$errors->get('end_time')" />
            </div>
        </div>

        <input type="hidden" name="is_active" value="0">
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" class="rounded-md border-slate-300 text-teal-800 focus:ring-teal-700/20" @checked(old('is_active', $halaqah?->is_active ?? true))>
            Kelas aktif
        </label>

        <div>
            <x-primary-button :disabled="$ustazList->isEmpty()">Simpan kelas</x-primary-button>
        </div>
    </form>
</x-app-layout>
