@props([
    'surahs' => null,
    'items' => null,
    'selected' => null,
    'name' => 'quran_surah_id',
    'allowEmpty' => false,
    'withJuz' => false,
])

@php
    if ($items === null) {
        $items = \App\Support\QuranCatalog::surahPickerItems(
            collect($surahs ?? []),
            withJuz: (bool) $withJuz,
        );
    }
    $items = collect($items)->values();
    $current = $items->firstWhere('id', (int) $selected);
@endphp

<div
    x-data="{
        open: false,
        allowEmpty: {{ $allowEmpty ? 'true' : 'false' }},
        query: {{ \Illuminate\Support\Js::from($current['label'] ?? '') }},
        selectedId: {{ \Illuminate\Support\Js::from($selected) }},
        juzFilter: '',
        items: {{ \Illuminate\Support\Js::from($items) }},
        get filtered() {
            let list = this.items;
            const juz = String(this.juzFilter || '').trim();
            if (juz !== '') {
                const n = Number(juz);
                list = list.filter((item) => {
                    const start = Number(item.juz ?? 0);
                    const end = Number(item.juz_end ?? item.juz ?? 0);
                    return n >= start && n <= end;
                });
            }
            const q = String(this.query || '').toLowerCase().trim();
            if (q) {
                list = list.filter((item) =>
                    item.label.toLowerCase().includes(q)
                    || String(item.id) === q
                    || item.name.toLowerCase().includes(q)
                    || String(item.juz_label || '').toLowerCase().includes(q)
                );
            }
            return list.slice(0, 30);
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
            this.$dispatch('surah-picked', { id: '', ayah: 286, juz: null, label: '' });
        },
        applyExternal(detail) {
            if (! detail?.id) {
                return;
            }
            const item = this.items.find((row) => Number(row.id) === Number(detail.id));
            if (item) {
                this.pick({ ...item, ...detail });
                return;
            }
            this.selectedId = detail.id;
            this.query = detail.label || String(detail.id);
            this.$dispatch('surah-picked', detail);
        }
    }"
    class="relative"
    @click.outside="open = false"
    @set-surah.window="applyExternal($event.detail)"
    @juz-filter.window="juzFilter = $event.detail ?? ''"
>
    <input type="hidden" name="{{ $name }}" x-model="selectedId" value="{{ $selected }}">
    <input
        type="search"
        x-model="query"
        @focus="open = true"
        @input="open = true"
        class="ui-input mt-1.5"
        placeholder="{{ $allowEmpty ? 'Semua surat atau cari nama / juz' : 'Cari nomor, nama surat, atau juz' }}"
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
</div>
