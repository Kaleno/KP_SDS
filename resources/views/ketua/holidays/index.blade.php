<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Kalender</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Tanggal merah</h1>
        </div>
    </x-slot>

    <div class="grid w-full max-w-5xl items-start gap-5 lg:grid-cols-2">
        <form method="POST" action="{{ route('ketua.holidays.store') }}" class="ui-card grid gap-4 p-5">
            @csrf
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <x-input-label for="date_from" value="Dari tanggal" />
                    <x-text-input id="date_from" name="date_from" type="date" class="mt-1.5" :value="old('date_from')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('date_from')" />
                </div>
                <div>
                    <x-input-label for="date_to" value="Sampai tanggal" />
                    <x-text-input id="date_to" name="date_to" type="date" class="mt-1.5" :value="old('date_to')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('date_to')" />
                </div>
            </div>
            <div>
                <x-input-label for="name" value="Nama libur" />
                <x-text-input id="name" name="name" class="mt-1.5" :value="old('name')" required />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>
            <x-primary-button>Tambah</x-primary-button>
        </form>

        <div class="space-y-2">
            @forelse ($holidays as $holiday)
                <div class="ui-card flex items-center justify-between gap-3 p-4">
                    <div class="min-w-0">
                        <p class="font-medium text-teal-950">{{ $holiday->name }}</p>
                        <p class="text-sm text-slate-500">{{ $holiday->date->format('d/m/Y') }}</p>
                    </div>
                    <form method="POST" action="{{ route('ketua.holidays.destroy', $holiday) }}" class="shrink-0">
                        @csrf
                        @method('DELETE')
                        <button class="text-sm font-semibold text-rose-700">Hapus</button>
                    </form>
                </div>
            @empty
                <x-empty>Belum ada tanggal merah.</x-empty>
            @endforelse
        </div>
    </div>
</x-app-layout>
