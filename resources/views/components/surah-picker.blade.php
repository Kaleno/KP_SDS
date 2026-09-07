@props(['surahs', 'selected' => null, 'name' => 'quran_surah_id', 'allowEmpty' => false])

@php
    $items = $surahs->map(fn ($surah): array => [
        'id' => $surah->id,
        'name' => $surah->name_id,
        'ayah' => $surah->ayah_count,
        'label' => $surah->id.'. '.$surah->name_id.' ('.$surah->ayah_count.' ayat)',
    ])->values();
    $current = $items->firstWhere('id', (int) $selected);
@endphp

<div
    x-data="{
        open: false,
        allowEmpty: {{ $allowEmpty ? 'true' : 'false' }},
        query: {{ \Illuminate\Support\Js::from($current['label'] ?? '') }},
        selectedId: {{ \Illuminate\Support\Js::from($selected) }},
        items: {{ \Illuminate\Support\Js::from($items) }},
        get filtered() {
            const q = String(this.query || '').toLowerCase().trim();
            const list = q
                ? this.items.filter((item) => item.label.toLowerCase().includes(q) || String(item.id) === q || item.name.toLowerCase().includes(q))
                : this.items;
            return list.slice(0, 20);
        },
        pick(item) {
            this.selectedId = item.id;
            this.query = item.label;
            this.open = false;
            this.$dispatch('surah-picked', item);
        },
        clear() {
            this.selectedId = '';
            this.query = '';
            this.open = false;
        }
    }"
    class="relative"
    @click.outside="open = false"
>
    <input type="hidden" name="{{ $name }}" x-model="selectedId" value="{{ $selected }}">
    <input
        type="search"
        x-model="query"
        @focus="open = true"
        @input="open = true"
        class="ui-input mt-1.5"
        placeholder="{{ $allowEmpty ? 'Semua surat atau cari nama' : 'Cari nomor atau nama surat' }}"
        autocomplete="off"
        aria-label="Cari surat"
    >
    <div x-show="open" x-cloak class="absolute z-20 mt-1 max-h-64 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lift">
        <button type="button" class="block w-full px-3 py-2.5 text-left text-sm text-slate-500 hover:bg-teal-50" x-show="allowEmpty" @click="clear()">Semua surat</button>
        <template x-for="item in filtered" :key="item.id">
            <button type="button" class="block w-full px-3 py-2.5 text-left text-sm hover:bg-teal-50" @click="pick(item)" x-text="item.label"></button>
        </template>
        <p class="px-3 py-2 text-sm text-slate-500" x-show="filtered.length === 0">Surat tidak ditemukan.</p>
    </div>
    @unless ($allowEmpty)
        <p class="mt-1 text-xs text-slate-400">Ketik nama atau nomor surat, lalu pilih dari daftar.</p>
    @endunless
</div>
