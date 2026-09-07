<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Setoran</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">
                {{ ($session ?? null) ? 'Setor santri hadir' : 'Input setoran' }}
            </h1>
            <p class="text-sm text-slate-500">
                @if ($session ?? null)
                    {{ $session->schedule->halaqah->name }} · {{ $session->session_date->format('d/m/Y') }}
                @else
                    Catat hafalan santri
                @endif
            </p>
        </div>
    </x-slot>

    <div class="max-w-lg">
        @if ($members->isEmpty())
            @if ($session ?? null)
                <x-empty>Semua santri yang hadir di sesi ini sudah tercatat setorannya hari ini, atau belum ada yang berstatus hadir.</x-empty>
            @else
                <x-empty>Tidak ada santri aktif di halaqah Anda.</x-empty>
            @endif
        @else
            @if ($session ?? null)
                <p class="mb-3 text-sm text-slate-600">
                    Hanya santri <span class="font-semibold">Hadir</span> yang belum setor hari ini.
                    Cari surat dengan mengetik nama atau nomor.
                </p>
            @endif
            <form method="POST" action="{{ route('ops.setoran.store') }}" class="ui-card space-y-4 p-5"
                  x-data="{
                      ayahMax: 286,
                      ayahStart: {{ \Illuminate\Support\Js::from(old('ayah_start', '')) }},
                      nextAyahBySantri: {{ \Illuminate\Support\Js::from($nextAyahBySantri ?: new \stdClass) }},
                      fillAyahStart(surahId = null, ayahCount = null) {
                          if (ayahCount) {
                              this.ayahMax = ayahCount;
                          }
                          const santriId = document.getElementById('santri_id')?.value;
                          const sid = String(surahId ?? document.querySelector('[name=quran_surah_id]')?.value ?? '');
                          if (! santriId || ! sid) {
                              return;
                          }
                          const bySantri = this.nextAyahBySantri[santriId] ?? this.nextAyahBySantri[Number(santriId)] ?? {};
                          const next = bySantri[sid] ?? bySantri[Number(sid)];
                          this.ayahStart = next ?? 1;
                      }
                  }"
                  @surah-picked.window="fillAyahStart($event.detail.id, $event.detail.ayah)">
                @csrf
                @include('ops.setoran.form')
                <button type="submit" class="btn-primary btn-block min-h-14 text-base">
                    Simpan setoran
                </button>
            </form>
        @endif

        <p class="mt-4 text-center">
            <a href="{{ route('ops.setoran.index') }}" class="ui-link text-sm">Kembali</a>
        </p>
    </div>
</x-app-layout>
