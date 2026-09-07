<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Setoran</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Koreksi setoran</h1>
            <p class="text-sm text-slate-500">{{ $setoran->setoran_date->format('d/m/Y') }}</p>
        </div>
    </x-slot>

    <div class="max-w-lg">
        <form method="POST" action="{{ route('ops.setoran.update', $setoran) }}" class="ui-card space-y-4 p-5"
              x-data="{ ayahMax: {{ $setoran->surah->ayah_count ?? 286 }} }"
              @surah-picked.window="ayahMax = $event.detail.ayah">
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
