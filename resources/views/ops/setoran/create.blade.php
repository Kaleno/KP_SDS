<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Input setoran</h1>
        <p class="text-sm text-slate-500">Catat hafalan santri</p>
    </x-slot>

    <div class="max-w-lg">
        @if ($members->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 p-5 text-sm text-slate-600">
                Tidak ada santri aktif di halaqah Anda.
            </div>
        @else
            <form method="POST" action="{{ route('ops.setoran.store') }}" class="bg-white rounded-2xl border border-slate-200 p-4 space-y-4">
                @csrf
                @include('ops.setoran.form')
                <button type="submit" class="w-full min-h-14 rounded-xl bg-teal-700 text-white font-semibold text-base">
                    Simpan setoran
                </button>
            </form>
        @endif

        <p class="mt-4 text-center">
            <a href="{{ route('ops.setoran.index') }}" class="text-sm text-teal-700 font-medium">Kembali</a>
        </p>
    </div>
</x-app-layout>
