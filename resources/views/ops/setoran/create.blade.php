<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Setoran</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Input setoran</h1>
            <p class="text-sm text-slate-500">Catat hafalan santri</p>
        </div>
    </x-slot>

    <div class="max-w-lg">
        @if ($members->isEmpty())
            <x-empty>Tidak ada santri aktif di halaqah Anda.</x-empty>
        @else
            <form method="POST" action="{{ route('ops.setoran.store') }}" class="ui-card p-5 space-y-4">
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
