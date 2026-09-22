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
            <select id="santri_id" name="santri_id" required class="mt-1.5 ui-input" @change="syncTrack(); fillAyahStart()">
                <option value="">Pilih santri</option>
                @foreach ($members as $member)
                    <option value="{{ $member->santri_id }}" @selected(old('santri_id', $members->count() === 1 ? $members->first()->santri_id : null) == $member->santri_id)>
                        {{ $member->santri->user->name }} — {{ $member->santri->track?->label() }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('santri_id')" />
        </div>
    @endif

    @if ($session ?? null)
        <input type="hidden" name="setoran_date" value="{{ $session->session_date->toDateString() }}">
        <div class="rounded-2xl bg-cream-100 px-4 py-3 text-sm text-slate-600">
            Tanggal sesi {{ $session->session_date->format('d/m/Y') }} — penilaian mengikuti tanggal absensi.
        </div>
    @else
        <div>
            <x-input-label for="setoran_date" value="Tanggal" />
            <x-text-input id="setoran_date" type="date" name="setoran_date" class="mt-1.5"
                          :value="old('setoran_date', $setoran?->setoran_date?->toDateString() ?? now()->toDateString())" required />
            <x-input-error class="mt-2" :messages="$errors->get('setoran_date')" />
        </div>
    @endif

    <div x-show="track === 'iqro'" x-cloak class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <x-input-label for="iqro_level" value="Iqro level" />
                <select id="iqro_level" name="iqro_level" class="mt-1.5 ui-input" x-bind:disabled="track !== 'iqro'">
                    @for ($level = 1; $level <= 6; $level++)
                        <option value="{{ $level }}" @selected((int) old('iqro_level', $setoran?->iqro_level ?? 1) === $level)>Iqro {{ $level }}</option>
                    @endfor
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('iqro_level')" />
            </div>
            <div>
                <x-input-label for="iqro_page" value="Halaman" />
                <x-text-input id="iqro_page" type="number" min="1" max="100" name="iqro_page" class="mt-1.5"
                              :value="old('iqro_page', $setoran?->iqro_page)" x-bind:disabled="track !== 'iqro'" />
                <x-input-error class="mt-2" :messages="$errors->get('iqro_page')" />
            </div>
        </div>
    </div>

    <div x-show="track === 'alquran'" x-cloak class="space-y-4">
        <div>
            <x-input-label for="quran_surah_id" value="Surat" />
            <x-surah-picker :surahs="$surahs" :selected="old('quran_surah_id', $setoran?->quran_surah_id)" />
            <x-input-error class="mt-2" :messages="$errors->get('quran_surah_id')" />
            @if (now()->isFriday())
                <p class="mt-1 text-xs text-slate-500">Jumat: hanya hafalan juz 30 (An-Naba s.d. An-Nas).</p>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <x-input-label for="ayah_start" value="Ayat awal" />
                @if ($isEdit)
                    <x-text-input id="ayah_start" type="number" min="1" name="ayah_start" class="mt-1.5"
                                  x-bind:max="ayahMax"
                                  :value="old('ayah_start', $setoran?->ayah_start)" />
                @else
                    <x-text-input id="ayah_start" type="number" min="1" name="ayah_start" class="mt-1.5"
                                  x-bind:max="ayahMax"
                                  x-model="ayahStart" />
                @endif
                <x-input-error class="mt-2" :messages="$errors->get('ayah_start')" />
            </div>
            <div>
                <x-input-label for="ayah_end" value="Ayat akhir" />
                <x-text-input id="ayah_end" type="number" min="1" name="ayah_end" class="mt-1.5"
                              x-bind:max="ayahMax"
                              :value="old('ayah_end', $setoran?->ayah_end)" />
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
