<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Setoran</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">
                {{ ($session ?? null) ? 'Setor santri hadir' : 'Input setoran' }}
            </h1>
            <p class="text-sm text-slate-500">
                @if ($session ?? null)
                    {{ \App\Support\DateLabel::long($session->session_date) }}
                @else
                    Bacaan atau hafalan — pilih jenis lalu isi detail
                @endif
            </p>
        </div>
    </x-slot>

    <div class="max-w-lg">
        @if ($members->isEmpty())
            @if ($session ?? null)
                <x-empty>Belum ada santri berstatus hadir di sesi ini.</x-empty>
            @else
                <x-empty>Tidak ada santri aktif untuk disetor.</x-empty>
            @endif
        @else
            @if ($session ?? null)
                <p class="mb-3 text-sm text-slate-600">
                    Santri <span class="font-semibold">Hadir</span> bisa dicatat lebih dari satu setoran (bacaan/hafalan).
                    Form mengisi lanjutan dari setoran terakhir per jenis.
                </p>
            @endif
            <form method="POST" action="{{ route('ops.setoran.store') }}" class="ui-card space-y-4 p-5"
                  x-data="{
                      category: {{ \Illuminate\Support\Js::from($defaultCategory) }},
                      subtype: {{ \Illuminate\Support\Js::from($defaultSubtype) }},
                      ayahMax: 286,
                      ayahStart: {{ \Illuminate\Support\Js::from(old('ayah_start', '')) }},
                      ayahEnd: {{ \Illuminate\Support\Js::from(old('ayah_end', '')) }},
                      iqroLevel: {{ \Illuminate\Support\Js::from((string) old('iqro_level', '1')) }},
                      iqroPage: {{ \Illuminate\Support\Js::from(old('iqro_page', '')) }},
                      doaName: {{ \Illuminate\Support\Js::from(old('doa_name', '')) }},
                      juzFilter: '',
                      selectedJuz: '',
                      progressHint: '',
                      nextAyahBySantri: {{ \Illuminate\Support\Js::from($nextAyahBySantri ?: new \stdClass) }},
                      continueBySantri: {{ \Illuminate\Support\Js::from($continueBySantri ?: new \stdClass) }},
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
                          this.applyContinueProgress();
                      },
                      applyContinueProgress() {
                          const santriId = document.getElementById('santri_id')?.value;
                          this.progressHint = '';
                          if (! santriId) {
                              return;
                          }
                          const bySantri = this.continueBySantri[santriId]
                              ?? this.continueBySantri[Number(santriId)]
                              ?? {};
                          const row = bySantri[this.subtype];
                          if (! row) {
                              return;
                          }
                          this.progressHint = row.hint || '';
                          if (this.subtype === 'iqro') {
                              this.iqroLevel = String(row.iqro_level);
                              this.iqroPage = String(row.iqro_page);
                              return;
                          }
                          if (this.subtype === 'doa') {
                              this.doaName = row.doa_name || '';
                              return;
                          }
                          if (this.subtype === 'alquran' || this.subtype === 'juz30') {
                              this.ayahMax = row.ayah_max || 286;
                              this.ayahStart = row.ayah_start || 1;
                              this.ayahEnd = '';
                              this.selectedJuz = row.juz ? String(row.juz) : '';
                              if (this.subtype === 'alquran' && row.juz) {
                                  this.juzFilter = String(row.juz);
                                  this.$dispatch('juz-filter', this.juzFilter);
                              }
                              this.$nextTick(() => {
                                  this.$dispatch('set-surah', {
                                      id: row.quran_surah_id,
                                      ayah: row.ayah_max,
                                      juz: row.juz,
                                      label: row.surah_label,
                                  });
                              });
                          }
                      },
                      fillAyahStart(surahId = null, ayahCount = null, juz = null) {
                          if (ayahCount) {
                              this.ayahMax = ayahCount;
                          }
                          if (juz) {
                              this.selectedJuz = String(juz);
                          }
                          const santriId = document.getElementById('santri_id')?.value;
                          const sid = String(surahId ?? document.querySelector('[name=quran_surah_id]')?.value ?? '');
                          if (! santriId || ! sid) {
                              this.refreshSelectedJuz();
                              return;
                          }
                          const bySantri = this.nextAyahBySantri[santriId] ?? this.nextAyahBySantri[Number(santriId)] ?? {};
                          const next = bySantri[sid] ?? bySantri[Number(sid)];
                          if (next) {
                              this.ayahStart = next;
                          }
                          this.refreshSelectedJuz();
                      },
                      refreshSelectedJuz() {
                          const sid = document.querySelector('[name=quran_surah_id]')?.value;
                          const ayah = Number(this.ayahStart || 1);
                          if (! sid) {
                              return;
                          }
                          const key = String(sid) + ':' + String(ayah);
                          const juz = this.ayahToJuz[key];
                          if (juz) {
                              this.selectedJuz = String(juz);
                          }
                      }
                  }"
                  x-init="
                      if (document.getElementById('santri_id')?.value) {
                          applyContinueProgress();
                      }
                  "
                  @surah-picked.window="fillAyahStart($event.detail.id, $event.detail.ayah, $event.detail.juz)">
                @csrf
                @include('ops.setoran.form')
                <button type="submit" class="btn-primary btn-block min-h-14 text-base">
                    Simpan setoran
                </button>
            </form>
        @endif

        <p class="mt-4 text-center">
            @if ($session ?? null)
                <a href="{{ route('ops.setoran.index') }}" class="ui-link text-sm">Selesai · lihat riwayat</a>
            @else
                <a href="{{ route('ops.setoran.index') }}" class="ui-link text-sm">Kembali</a>
            @endif
        </p>
    </div>
</x-app-layout>
