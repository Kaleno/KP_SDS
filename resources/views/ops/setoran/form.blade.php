@php
    $isEdit = (bool) $setoran;
    $defaultStatus = old('status', $setoran?->status?->value ?? 'lulus');
@endphp

<div class="space-y-4">
    @if ($isEdit)
        <div class="rounded-2xl bg-cream-100 px-4 py-3 text-sm text-slate-600">
            {{ $setoran->santri->user->name }} · NIS {{ $setoran->santri->nis }}
            · {{ $setoran->santri->track?->label() }}
        </div>
    @else
        @if (($session ?? null))
            <input type="hidden" name="sesi" value="{{ $session->id }}">
        @endif
        <div>
            <x-input-label for="santri_id" value="Santri" />
            <select id="santri_id" name="santri_id" required class="mt-1.5 ui-input"
                    @change="applyContinueProgress()">
                <option value="">Pilih santri</option>
                @foreach ($members as $member)
                    <option value="{{ $member->id }}" @selected(old('santri_id', $members->count() === 1 ? $members->first()->id : null) == $member->id)>
                        {{ $member->user->name }} — {{ $member->track?->label() }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('santri_id')" />
        </div>
    @endif

    @if ($session ?? null)
        <input type="hidden" name="setoran_date" value="{{ $session->session_date->toDateString() }}">
        <div class="rounded-2xl bg-cream-100 px-4 py-3 text-sm text-slate-600">
            Tanggal sesi {{ \App\Support\DateLabel::long($session->session_date) }} — penilaian mengikuti tanggal absensi.
        </div>
    @else
        <div>
            <x-input-label for="setoran_date" value="Tanggal" />
            <x-text-input id="setoran_date" type="date" name="setoran_date" class="mt-1.5"
                          :value="old('setoran_date', $setoran?->setoran_date?->toDateString() ?? now()->toDateString())" required />
            <x-input-error class="mt-2" :messages="$errors->get('setoran_date')" />
        </div>
    @endif

    <div>
        <p class="ui-label mb-2">Jenis setoran</p>
        <div class="grid grid-cols-2 gap-2">
            @foreach ($categories as $category)
                <label class="block">
                    <input type="radio" name="category" value="{{ $category->value }}" class="peer sr-only"
                           x-model="category" @change="onCategoryChange()">
                    <span class="ui-choice text-xs sm:text-sm">{{ $category->label() }}</span>
                </label>
            @endforeach
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('category')" />
    </div>

    <div>
        <p class="ui-label mb-2">Subtipe</p>
        <div class="grid grid-cols-2 gap-2">
            <template x-for="option in subtypeOptions" :key="option.value">
                <label class="block">
                    <input type="radio" name="subtype" :value="option.value" class="peer sr-only"
                           x-model="subtype" @change="applyContinueProgress()">
                    <span class="ui-choice text-xs sm:text-sm" x-text="option.label"></span>
                </label>
            </template>
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('subtype')" />
    </div>

    <div x-show="progressHint" x-cloak class="rounded-2xl bg-teal-50 px-4 py-3 text-sm text-teal-900">
        <span x-text="progressHint"></span>
    </div>

    <div x-show="subtype === 'iqro'" x-cloak class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <x-input-label for="iqro_level" value="Iqro level" />
                <select id="iqro_level" name="iqro_level" class="mt-1.5 ui-input"
                        x-model="iqroLevel" x-bind:disabled="subtype !== 'iqro'">
                    @for ($level = 1; $level <= 6; $level++)
                        <option value="{{ $level }}">Iqro {{ $level }}</option>
                    @endfor
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('iqro_level')" />
            </div>
            <div>
                <x-input-label for="iqro_page" value="Halaman" />
                <input id="iqro_page" type="number" min="1" max="100" name="iqro_page" class="mt-1.5 ui-input"
                       x-model="iqroPage" x-bind:disabled="subtype !== 'iqro'" />
                <x-input-error class="mt-2" :messages="$errors->get('iqro_page')" />
            </div>
        </div>
    </div>

    <div x-show="subtype === 'doa'" x-cloak>
        <x-input-label for="doa_name" value="Nama doa" />
        <input id="doa_name" name="doa_name" class="mt-1.5 ui-input"
               x-model="doaName"
               x-bind:disabled="subtype !== 'doa'"
               placeholder="Nama doa" />
        <x-input-error class="mt-2" :messages="$errors->get('doa_name')" />
    </div>

    <div x-show="subtype === 'alquran' || subtype === 'juz30'" x-cloak class="space-y-4">
        <div x-show="subtype === 'alquran'" x-cloak>
            <x-input-label for="juz_filter" value="Filter Juz" />
            <select id="juz_filter" class="mt-1.5 ui-input" x-model="juzFilter"
                    @change="$dispatch('juz-filter', juzFilter)">
                <option value="">Semua juz</option>
                @for ($juz = 1; $juz <= 30; $juz++)
                    <option value="{{ $juz }}">Juz {{ $juz }}</option>
                @endfor
            </select>
        </div>

        <div>
            <x-input-label for="quran_surah_id" value="Surat" />
            <template x-if="subtype === 'alquran'">
                <div>
                    <x-surah-picker
                        :items="$surahPickerItems ?? \App\Support\QuranCatalog::surahPickerItems($surahs, true)"
                        :selected="old('quran_surah_id', $setoran?->quran_surah_id)"
                        :with-juz="true"
                    />
                </div>
            </template>
            <template x-if="subtype === 'juz30'">
                <div>
                    <x-surah-picker
                        :items="$juz30PickerItems ?? \App\Support\QuranCatalog::surahPickerItems($juz30Surahs, true)"
                        :selected="old('quran_surah_id', $setoran?->quran_surah_id)"
                        :with-juz="true"
                    />
                </div>
            </template>
            <p x-show="selectedJuz" x-cloak class="mt-1 text-xs font-medium text-teal-800">
                Juz bacaan: <span x-text="selectedJuz"></span>
            </p>
            <x-input-error class="mt-2" :messages="$errors->get('quran_surah_id')" />
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <x-input-label for="ayah_start" value="Ayat awal" />
                @if ($isEdit)
                    <x-text-input id="ayah_start" type="number" min="1" name="ayah_start" class="mt-1.5"
                                  x-bind:max="ayahMax"
                                  :value="old('ayah_start', $setoran?->ayah_start)"
                                  x-bind:disabled="subtype !== 'alquran' && subtype !== 'juz30'" />
                @else
                    <input id="ayah_start" type="number" min="1" name="ayah_start" class="mt-1.5 ui-input"
                           x-bind:max="ayahMax"
                           x-model="ayahStart"
                           @change="refreshSelectedJuz()"
                           x-bind:disabled="subtype !== 'alquran' && subtype !== 'juz30'" />
                @endif
                <x-input-error class="mt-2" :messages="$errors->get('ayah_start')" />
            </div>
            <div>
                <x-input-label for="ayah_end" value="Ayat akhir" />
                <input id="ayah_end" type="number" min="1" name="ayah_end" class="mt-1.5 ui-input"
                       x-bind:max="ayahMax"
                       x-model="ayahEnd"
                       x-bind:disabled="subtype !== 'alquran' && subtype !== 'juz30'" />
                <x-input-error class="mt-2" :messages="$errors->get('ayah_end')" />
            </div>
        </div>
    </div>

    <div>
        <p class="ui-label mb-2">Status</p>
        <div class="grid grid-cols-2 gap-2">
            @foreach ($statuses as $status)
                <label class="block">
                    <input type="radio" name="status" value="{{ $status->value }}" class="peer sr-only"
                           @checked($defaultStatus === $status->value)>
                    <span class="ui-choice text-xs sm:text-sm {{ $status->buttonClass() }}">
                        {{ $status->label() }}
                    </span>
                </label>
            @endforeach
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('status')" />
    </div>

    <div>
        <x-input-label for="note" value="Catatan" />
        <textarea id="note" name="note" rows="2" class="mt-1.5 ui-input">{{ old('note', $setoran?->note) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('note')" />
    </div>

    @if ($isEdit)
        <div>
            <x-input-label for="correction_note" value="Alasan koreksi" />
            <textarea id="correction_note" name="correction_note" rows="2" required class="mt-1.5 ui-input">{{ old('correction_note', $setoran?->correction_note) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('correction_note')" />
        </div>
    @endif
</div>
