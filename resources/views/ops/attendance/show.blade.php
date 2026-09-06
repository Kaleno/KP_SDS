<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-slate-800">{{ $session->schedule->halaqah->name }}</h1>
            <p class="text-sm text-slate-500">
                {{ $session->session_date->format('d/m/Y') }} · {{ $session->schedule->timeRange() }}
                · {{ $session->schedule->location->name }}
            </p>
        </div>
    </x-slot>

    <div class="max-w-lg">
        @if ($session->attendances->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 p-5 text-sm text-slate-600">
                Belum ada anggota aktif di halaqah ini. Minta Ketua menambahkan santri.
            </div>
        @else
            <form method="POST" action="{{ route('ops.attendance.update', $session) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <x-input-error :messages="$errors->get('rows')" class="mb-2" />

                @foreach ($session->attendances->sortBy('santri.user.name') as $row)
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-3">
                        <div>
                            <p class="font-semibold text-slate-800">{{ $row->santri->user->name }}</p>
                            <p class="text-sm text-slate-500">NIS {{ $row->santri->nis }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach ($statuses as $status)
                                <label class="block">
                                    <input type="radio"
                                           name="rows[{{ $row->santri_id }}][status]"
                                           value="{{ $status->value }}"
                                           class="peer sr-only"
                                           @checked(old('rows.'.$row->santri_id.'.status', $row->status->value) === $status->value)>
                                    <span class="flex items-center justify-center min-h-12 rounded-xl border border-slate-300 text-base font-semibold text-slate-700 {{ $status->buttonClass() }}">
                                        {{ $status->label() }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <input type="text"
                               name="rows[{{ $row->santri_id }}][note]"
                               value="{{ old('rows.'.$row->santri_id.'.note', $row->note) }}"
                               placeholder="Catatan (opsional)"
                               class="w-full rounded-xl border-slate-300 text-base min-h-12">
                    </div>
                @endforeach

                <button type="submit"
                        class="w-full min-h-14 rounded-xl bg-teal-700 text-white font-semibold text-base">
                    Simpan absensi
                </button>
            </form>
        @endif

        <p class="mt-4 text-center">
            <a href="{{ route('ops.attendance.index') }}" class="text-sm text-teal-700 font-medium">Kembali ke daftar sesi</a>
        </p>
    </div>
</x-app-layout>
