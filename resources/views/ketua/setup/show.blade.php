@php
    $yearDefault = $years->isNotEmpty() ? 'existing' : 'new';
    $locationDefault = $locations->isNotEmpty() ? 'existing' : 'new';
    $ustazDefault = $ustazList->isNotEmpty() ? 'existing' : 'new';
    $santriDefault = $santriList->isNotEmpty() ? 'existing' : 'new';

    $stepErrors = [
        0 => $errors->hasAny(['year_source', 'academic_year_id', 'year_name', 'year_start_date', 'year_end_date']),
        1 => $errors->hasAny(['location_source', 'location_id', 'location_name', 'location_description']),
        2 => $errors->hasAny(['ustaz_source', 'ustaz_user_id', 'ustaz_name', 'ustaz_username', 'ustaz_email', 'ustaz_password']),
        3 => $errors->hasAny(['santri_source', 'santri_id', 'santri_name', 'santri_nis', 'santri_email', 'santri_gender', 'santri_password']),
        4 => $errors->hasAny(['halaqah_name', 'day_of_week', 'start_time', 'end_time']),
    ];
    $startStep = collect($stepErrors)->search(true);
    if ($startStep === false) {
        $startStep = 0;
    }

    $yearStart = now()->month >= 7
        ? now()->copy()->month(7)->startOfMonth()
        : now()->copy()->subYear()->month(7)->startOfMonth();
    $yearEnd = $yearStart->copy()->addYear()->subDay();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Persiapan</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Siapkan halaqah</h1>
            <p class="text-sm text-slate-500">Satu kelompok, satu jadwal, satu santri. Anggota lain bisa ditambah nanti.</p>
        </div>
    </x-slot>

    <div class="max-w-xl"
         x-data="{
            step: {{ (int) $startStep }},
            yearSource: {{ \Illuminate\Support\Js::from(old('year_source', $yearDefault)) }},
            locationSource: {{ \Illuminate\Support\Js::from(old('location_source', $locationDefault)) }},
            ustazSource: {{ \Illuminate\Support\Js::from(old('ustaz_source', $ustazDefault)) }},
            santriSource: {{ \Illuminate\Support\Js::from(old('santri_source', $santriDefault)) }},
            next() { if (this.step < 4) this.step++ },
            prev() { if (this.step > 0) this.step-- },
         }">
        <div class="mb-4 flex items-center justify-between text-sm text-slate-500">
            <p>Langkah <span x-text="step + 1"></span> dari 5</p>
            <p class="font-medium text-teal-800" x-text="['Tahun ajaran', 'Lokasi', 'Pengajar', 'Santri', 'Halaqah'][step]"></p>
        </div>
        <div class="mb-5 h-1.5 overflow-hidden rounded-full bg-cream-100">
            <div class="h-full rounded-full bg-teal-700 transition-all" :style="'width: ' + ((step + 1) * 20) + '%'"></div>
        </div>

        @if ($readiness['ready'])
            <p class="mb-4 rounded-2xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900">
                Halaqah aktif sudah ada. Wizard ini menambah kelompok baru.
            </p>
        @endif

        <form method="POST" action="{{ route('ketua.setup.store') }}" class="ui-card space-y-5 p-5 sm:p-6" novalidate>
            @csrf

            <section x-show="step === 0" x-cloak>
                <h2 class="font-display text-xl font-semibold text-teal-950">Tahun ajaran</h2>
                <p class="mt-1 text-sm text-slate-500">Hanya satu tahun yang aktif. Tahun baru akan menggantikan yang lama.</p>
                @if ($years->isEmpty())
                    <input type="hidden" name="year_source" value="new">
                    <p class="mt-4 rounded-2xl bg-cream-100 px-4 py-3 text-sm text-slate-600">Belum ada tahun ajaran. Form ini membuat yang pertama dan mengaktifkannya.</p>
                @else
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <label class="block">
                            <input type="radio" name="year_source" value="existing" class="peer sr-only" x-model="yearSource" @checked(old('year_source', $yearDefault) === 'existing')>
                            <span class="ui-choice text-sm">Pakai yang ada</span>
                        </label>
                        <label class="block">
                            <input type="radio" name="year_source" value="new" class="peer sr-only" x-model="yearSource" @checked(old('year_source', $yearDefault) === 'new')>
                            <span class="ui-choice text-sm">Buat baru</span>
                        </label>
                    </div>
                @endif
                <x-input-error class="mt-2" :messages="$errors->get('year_source')" />

                @if ($years->isNotEmpty())
                    <div class="mt-4" x-show="yearSource === 'existing'">
                        <x-input-label for="academic_year_id" value="Pilih tahun" />
                        <select id="academic_year_id" name="academic_year_id" class="mt-1.5 ui-input">
                            @foreach ($years as $year)
                                <option value="{{ $year->id }}" @selected(old('academic_year_id', $year->is_active ? $year->id : null) == $year->id)>
                                    {{ $year->name }}{{ $year->is_active ? ' (aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('academic_year_id')" />
                    </div>
                @endif

                <div class="mt-4 space-y-3" x-show="yearSource === 'new'">
                    <div>
                        <x-input-label for="year_name" value="Nama" />
                        <x-text-input id="year_name" name="year_name" class="mt-1.5" :value="old('year_name', $yearStart->year.'/'.$yearEnd->year)" placeholder="2026/2027" />
                        <x-input-error class="mt-2" :messages="$errors->get('year_name')" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <x-input-label for="year_start_date" value="Mulai" />
                            <x-text-input id="year_start_date" name="year_start_date" type="date" class="mt-1.5" :value="old('year_start_date', $yearStart->toDateString())" />
                            <x-input-error class="mt-2" :messages="$errors->get('year_start_date')" />
                        </div>
                        <div>
                            <x-input-label for="year_end_date" value="Selesai" />
                            <x-text-input id="year_end_date" name="year_end_date" type="date" class="mt-1.5" :value="old('year_end_date', $yearEnd->toDateString())" />
                            <x-input-error class="mt-2" :messages="$errors->get('year_end_date')" />
                        </div>
                    </div>
                </div>
            </section>

            <section x-show="step === 1" x-cloak>
                <h2 class="font-display text-xl font-semibold text-teal-950">Lokasi pertemuan</h2>
                <p class="mt-1 text-sm text-slate-500">Tempat absensi dan setoran dibuka.</p>
                @if ($locations->isEmpty())
                    <input type="hidden" name="location_source" value="new">
                    <p class="mt-4 rounded-2xl bg-cream-100 px-4 py-3 text-sm text-slate-600">Belum ada lokasi. Tulis nama tempat pertemuan.</p>
                @else
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <label class="block">
                            <input type="radio" name="location_source" value="existing" class="peer sr-only" x-model="locationSource" @checked(old('location_source', $locationDefault) === 'existing')>
                            <span class="ui-choice text-sm">Pakai yang ada</span>
                        </label>
                        <label class="block">
                            <input type="radio" name="location_source" value="new" class="peer sr-only" x-model="locationSource" @checked(old('location_source', $locationDefault) === 'new')>
                            <span class="ui-choice text-sm">Buat baru</span>
                        </label>
                    </div>
                @endif
                <x-input-error class="mt-2" :messages="$errors->get('location_source')" />

                @if ($locations->isNotEmpty())
                    <div class="mt-4" x-show="locationSource === 'existing'">
                        <x-input-label for="location_id" value="Pilih lokasi" />
                        <select id="location_id" name="location_id" class="mt-1.5 ui-input">
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" @selected(old('location_id') == $location->id)>{{ $location->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('location_id')" />
                    </div>
                @endif

                <div class="mt-4 space-y-3" x-show="locationSource === 'new'">
                    <div>
                        <x-input-label for="location_name" value="Nama lokasi" />
                        <x-text-input id="location_name" name="location_name" class="mt-1.5" :value="old('location_name')" placeholder="Masjid Utama" />
                        <x-input-error class="mt-2" :messages="$errors->get('location_name')" />
                    </div>
                    <div>
                        <x-input-label for="location_description" value="Keterangan (opsional)" />
                        <x-text-input id="location_description" name="location_description" class="mt-1.5" :value="old('location_description')" />
                    </div>
                </div>
            </section>

            <section x-show="step === 2" x-cloak>
                <h2 class="font-display text-xl font-semibold text-teal-950">Pengajar</h2>
                <p class="mt-1 text-sm text-slate-500">Ustaz yang membuka absensi dan mencatat setoran.</p>
                @if ($ustazList->isEmpty())
                    <input type="hidden" name="ustaz_source" value="new">
                    <p class="mt-4 rounded-2xl bg-cream-100 px-4 py-3 text-sm text-slate-600">Belum ada pengajar. Buat akun ustaz untuk kelompok ini.</p>
                @else
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <label class="block">
                            <input type="radio" name="ustaz_source" value="existing" class="peer sr-only" x-model="ustazSource" @checked(old('ustaz_source', $ustazDefault) === 'existing')>
                            <span class="ui-choice text-sm">Pakai yang ada</span>
                        </label>
                        <label class="block">
                            <input type="radio" name="ustaz_source" value="new" class="peer sr-only" x-model="ustazSource" @checked(old('ustaz_source', $ustazDefault) === 'new')>
                            <span class="ui-choice text-sm">Buat akun</span>
                        </label>
                    </div>
                @endif
                <x-input-error class="mt-2" :messages="$errors->get('ustaz_source')" />

                @if ($ustazList->isNotEmpty())
                    <div class="mt-4" x-show="ustazSource === 'existing'">
                        <x-input-label for="ustaz_user_id" value="Pilih ustaz" />
                        <select id="ustaz_user_id" name="ustaz_user_id" class="mt-1.5 ui-input">
                            @foreach ($ustazList as $ustaz)
                                <option value="{{ $ustaz->id }}" @selected(old('ustaz_user_id') == $ustaz->id)>{{ $ustaz->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('ustaz_user_id')" />
                    </div>
                @endif

                <div class="mt-4 space-y-3" x-show="ustazSource === 'new'">
                    <div>
                        <x-input-label for="ustaz_name" value="Nama" />
                        <x-text-input id="ustaz_name" name="ustaz_name" class="mt-1.5" :value="old('ustaz_name')" />
                        <x-input-error class="mt-2" :messages="$errors->get('ustaz_name')" />
                    </div>
                    <div>
                        <x-input-label for="ustaz_username" value="Username" />
                        <x-text-input id="ustaz_username" name="ustaz_username" class="mt-1.5" :value="old('ustaz_username')" />
                        <x-input-error class="mt-2" :messages="$errors->get('ustaz_username')" />
                    </div>
                    <div>
                        <x-input-label for="ustaz_email" value="Email (opsional)" />
                        <x-text-input id="ustaz_email" name="ustaz_email" type="email" class="mt-1.5" :value="old('ustaz_email')" />
                        <x-input-error class="mt-2" :messages="$errors->get('ustaz_email')" />
                    </div>
                    <div>
                        <x-input-label for="ustaz_password" value="Kata sandi" />
                        <x-text-input id="ustaz_password" name="ustaz_password" type="password" class="mt-1.5" />
                        <x-input-error class="mt-2" :messages="$errors->get('ustaz_password')" />
                    </div>
                    <div>
                        <x-input-label for="ustaz_password_confirmation" value="Ulangi kata sandi" />
                        <x-text-input id="ustaz_password_confirmation" name="ustaz_password_confirmation" type="password" class="mt-1.5" />
                    </div>
                </div>
            </section>

            <section x-show="step === 3" x-cloak>
                <h2 class="font-display text-xl font-semibold text-teal-950">Santri pertama</h2>
                <p class="mt-1 text-sm text-slate-500">Minimal satu anggota agar sesi absensi bisa dibuka.</p>
                @if ($santriList->isEmpty())
                    <input type="hidden" name="santri_source" value="new">
                    <p class="mt-4 rounded-2xl bg-cream-100 px-4 py-3 text-sm text-slate-600">Belum ada santri. Buat akun pertama untuk anggota halaqah.</p>
                @else
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <label class="block">
                            <input type="radio" name="santri_source" value="existing" class="peer sr-only" x-model="santriSource" @checked(old('santri_source', $santriDefault) === 'existing')>
                            <span class="ui-choice text-sm">Pakai yang ada</span>
                        </label>
                        <label class="block">
                            <input type="radio" name="santri_source" value="new" class="peer sr-only" x-model="santriSource" @checked(old('santri_source', $santriDefault) === 'new')>
                            <span class="ui-choice text-sm">Buat baru</span>
                        </label>
                    </div>
                @endif
                <x-input-error class="mt-2" :messages="$errors->get('santri_source')" />

                @if ($santriList->isNotEmpty())
                    <div class="mt-4" x-show="santriSource === 'existing'">
                        <x-input-label for="santri_id" value="Pilih santri" />
                        <select id="santri_id" name="santri_id" class="mt-1.5 ui-input">
                            @foreach ($santriList as $santri)
                                <option value="{{ $santri->id }}" @selected(old('santri_id') == $santri->id)>
                                    {{ $santri->nis }} — {{ $santri->user->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('santri_id')" />
                    </div>
                @endif

                <div class="mt-4 space-y-3" x-show="santriSource === 'new'">
                    <div>
                        <x-input-label for="santri_name" value="Nama" />
                        <x-text-input id="santri_name" name="santri_name" class="mt-1.5" :value="old('santri_name')" />
                        <x-input-error class="mt-2" :messages="$errors->get('santri_name')" />
                    </div>
                    <div>
                        <x-input-label for="santri_nis" value="NIS (juga username login)" />
                        <x-text-input id="santri_nis" name="santri_nis" class="mt-1.5" :value="old('santri_nis')" />
                        <x-input-error class="mt-2" :messages="$errors->get('santri_nis')" />
                    </div>
                    <div>
                        <x-input-label for="santri_gender" value="Gender" />
                        <select id="santri_gender" name="santri_gender" class="mt-1.5 ui-input">
                            @foreach ($genders as $gender)
                                <option value="{{ $gender->value }}" @selected(old('santri_gender', $gender->value) === $gender->value)>{{ $gender->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('santri_gender')" />
                    </div>
                    <div>
                        <x-input-label for="santri_email" value="Email (opsional)" />
                        <x-text-input id="santri_email" name="santri_email" type="email" class="mt-1.5" :value="old('santri_email')" />
                    </div>
                    <div>
                        <x-input-label for="santri_password" value="Kata sandi" />
                        <x-text-input id="santri_password" name="santri_password" type="password" class="mt-1.5" />
                        <x-input-error class="mt-2" :messages="$errors->get('santri_password')" />
                    </div>
                    <div>
                        <x-input-label for="santri_password_confirmation" value="Ulangi kata sandi" />
                        <x-text-input id="santri_password_confirmation" name="santri_password_confirmation" type="password" class="mt-1.5" />
                    </div>
                </div>
            </section>

            <section x-show="step === 4" x-cloak>
                <h2 class="font-display text-xl font-semibold text-teal-950">Halaqah dan jadwal</h2>
                <p class="mt-1 text-sm text-slate-500">Satu slot mingguan cukup untuk mulai. Slot lain ditambah dari halaman halaqah.</p>
                <div class="mt-4 space-y-3">
                    <div>
                        <x-input-label for="halaqah_name" value="Nama kelompok" />
                        <x-text-input id="halaqah_name" name="halaqah_name" class="mt-1.5" :value="old('halaqah_name')" placeholder="Halaqah Tahfidz A" />
                        <x-input-error class="mt-2" :messages="$errors->get('halaqah_name')" />
                    </div>
                    <div>
                        <x-input-label for="day_of_week" value="Hari" />
                        <select id="day_of_week" name="day_of_week" class="mt-1.5 ui-input">
                            @foreach ($days as $value => $label)
                                <option value="{{ $value }}" @selected((int) old('day_of_week', now()->isoWeekday()) === (int) $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('day_of_week')" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <x-input-label for="start_time" value="Mulai" />
                            <x-text-input id="start_time" name="start_time" type="time" class="mt-1.5" :value="old('start_time', '07:00')" />
                            <x-input-error class="mt-2" :messages="$errors->get('start_time')" />
                        </div>
                        <div>
                            <x-input-label for="end_time" value="Selesai" />
                            <x-text-input id="end_time" name="end_time" type="time" class="mt-1.5" :value="old('end_time', '08:30')" />
                            <x-input-error class="mt-2" :messages="$errors->get('end_time')" />
                        </div>
                    </div>
                </div>
            </section>

            <div class="flex gap-2 pt-2">
                <button type="button" class="btn-secondary min-h-12 flex-1" x-show="step > 0" x-cloak @click="prev()">Kembali</button>
                <button type="button" class="btn-primary min-h-12 flex-1" x-show="step < 4" @click="next()">Lanjut</button>
                <button type="submit" class="btn-primary min-h-12 flex-1" x-show="step === 4" x-cloak>Siapkan halaqah</button>
            </div>
        </form>
    </div>
</x-app-layout>
