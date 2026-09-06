<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Koreksi setoran</h1>
        <p class="text-sm text-slate-500">{{ $setoran->setoran_date->format('d/m/Y') }}</p>
    </x-slot>

    <div class="max-w-lg">
        <form method="POST" action="{{ route('ops.setoran.update', $setoran) }}" class="bg-white rounded-2xl border border-slate-200 p-4 space-y-4">
            @csrf
            @method('PUT')
            @include('ops.setoran.form')
            <button type="submit" class="w-full min-h-14 rounded-xl bg-teal-700 text-white font-semibold text-base">
                Simpan koreksi
            </button>
        </form>

        <p class="mt-4 text-center">
            <a href="{{ route('ops.setoran.index') }}" class="text-sm text-teal-700 font-medium">Kembali</a>
        </p>
    </div>
</x-app-layout>
