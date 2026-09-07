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
                  x-data="{ ayahMax: 286 }"
                  @surah-picked.window="ayahMax = $event.detail.ayah">
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
