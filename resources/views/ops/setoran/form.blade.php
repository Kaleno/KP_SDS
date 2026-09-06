@php
    $isEdit = (bool) $setoran;
@endphp

<div class="space-y-4">
    @if ($isEdit)
        <div class="rounded-2xl bg-cream-100 px-4 py-3 text-sm text-slate-600">
            {{ $setoran->santri->user->name }} · NIS {{ $setoran->santri->nis }}
        </div>
    @else
        <div>
            <x-input-label for="santri_id" value="Santri" />
            <select id="santri_id" name="santri_id" required class="mt-1.5 ui-input">
                <option value="">Pilih santri</option>
                @foreach ($members as $member)
                    <option value="{{ $member->santri_id }}" @selected(old('santri_id') == $member->santri_id)>
                        {{ $member->santri->user->name }} — {{ $member->halaqah->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('santri_id')" />
        </div>
    @endif

    <div>
        <x-input-label for="setoran_date" value="Tanggal" />
        <x-text-input id="setoran_date" type="date" name="setoran_date" class="mt-1.5"
                      :value="old('setoran_date', $setoran?->setoran_date?->toDateString() ?? now()->toDateString())" required />
        <x-input-error class="mt-2" :messages="$errors->get('setoran_date')" />
    </div>

    <div>
        <x-input-label for="quran_surah_id" value="Surat" />
        <select id="quran_surah_id" name="quran_surah_id" required class="mt-1.5 ui-input">
            <option value="">Pilih surat</option>
            @foreach ($surahs as $surah)
                <option value="{{ $surah->id }}" @selected(old('quran_surah_id', $setoran?->quran_surah_id) == $surah->id)>
                    {{ $surah->id }}. {{ $surah->name_id }} ({{ $surah->ayah_count }} ayat)
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('quran_surah_id')" />
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <x-input-label for="ayah_start" value="Ayat awal" />
            <x-text-input id="ayah_start" type="number" min="1" name="ayah_start" class="mt-1.5"
                          :value="old('ayah_start', $setoran?->ayah_start)" required />
            <x-input-error class="mt-2" :messages="$errors->get('ayah_start')" />
        </div>
        <div>
            <x-input-label for="ayah_end" value="Ayat akhir" />
            <x-text-input id="ayah_end" type="number" min="1" name="ayah_end" class="mt-1.5"
                          :value="old('ayah_end', $setoran?->ayah_end)" required />
            <x-input-error class="mt-2" :messages="$errors->get('ayah_end')" />
        </div>
    </div>

    <div>
        <p class="ui-label mb-2">Status</p>
        <div class="grid grid-cols-3 gap-2">
            @foreach ($statuses as $status)
                <label class="block">
                    <input type="radio" name="status" value="{{ $status->value }}" class="peer sr-only"
                           @checked(old('status', $setoran?->status?->value ?? 'lancar') === $status->value)>
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
