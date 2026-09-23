<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Setoran</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Koreksi setoran</h1>
            <p class="text-sm text-slate-500">{{ \App\Support\DateLabel::long($setoran->setoran_date) }}</p>
        </div>
    </x-slot>

    <div class="max-w-lg">
        <form method="POST" action="{{ route('ops.setoran.update', $setoran) }}" class="ui-card space-y-4 p-5"
              x-data="{
                  category: {{ \Illuminate\Support\Js::from($defaultCategory) }},
                  subtype: {{ \Illuminate\Support\Js::from($defaultSubtype) }},
                  ayahMax: {{ $setoran->surah?->ayah_count ?? 286 }},
                  ayahStart: {{ \Illuminate\Support\Js::from(old('ayah_start', $setoran->ayah_start)) }},
                  ayahEnd: {{ \Illuminate\Support\Js::from(old('ayah_end', $setoran->ayah_end)) }},
                  iqroLevel: {{ \Illuminate\Support\Js::from((string) old('iqro_level', $setoran->iqro_level ?? '1')) }},
                  iqroPage: {{ \Illuminate\Support\Js::from(old('iqro_page', $setoran->iqro_page)) }},
                  doaName: {{ \Illuminate\Support\Js::from(old('doa_name', $setoran->doa_name)) }},
                  juzFilter: '',
                  selectedJuz: {{ \Illuminate\Support\Js::from(
                      $setoran->quran_surah_id && $setoran->ayah_start
                          ? (string) (\App\Support\QuranCatalog::juzForAyah((int) $setoran->quran_surah_id, (int) $setoran->ayah_start) ?? '')
                          : ''
                  ) }},
                  progressHint: '',
                  continueBySantri: {},
                  nextAyahBySantri: {},
                  ayahToJuz: {{ \Illuminate\Support\Js::from($ayahToJuz ?? new \stdClass) }},
                  get subtypeOptions() {
                      if (this.category === 'hafalan') {
                          return [
                              { value: 'doa', label: 'Doa' },
                              { value: 'juz30', label: 'Juz 30' },
                          ];
                      }
                      return [
                          { value: 'iqro', label: 'Iqro' },
                          { value: 'alquran', label: 'Alquran' },
                      ];
                  },
                  onCategoryChange() {
                      this.subtype = this.category === 'hafalan' ? 'doa' : 'iqro';
                  },
                  applyContinueProgress() {},
                  refreshSelectedJuz() {
                      const sid = document.querySelector('[name=quran_surah_id]')?.value;
                      const ayah = Number(this.ayahStart || 1);
                      if (! sid) {
                          return;
                      }
                      const juz = this.ayahToJuz[String(sid) + ':' + String(ayah)];
                      if (juz) {
                          this.selectedJuz = String(juz);
                      }
                  }
              }"
              @surah-picked.window="ayahMax = $event.detail.ayah; if ($event.detail.juz) selectedJuz = String($event.detail.juz)">
            @csrf
            @method('PUT')
            @include('ops.setoran.form')
            <button type="submit" class="btn-primary btn-block min-h-14 text-base">
                Simpan koreksi
            </button>
        </form>

        <p class="mt-4 text-center">
            <a href="{{ route('ops.setoran.index') }}" class="ui-link text-sm">Kembali</a>
        </p>
    </div>
</x-app-layout>
