<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Absensi</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">{{ $session->schedule->halaqah->name }}</h1>
            <p class="text-sm text-slate-500">
                {{ $session->session_date->format('d/m/Y') }} · {{ $session->schedule->timeRange() }}
                · {{ $session->schedule->location->name }}
            </p>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        @if ($session->attendances->isEmpty())
            <x-empty>
                Belum ada anggota aktif di halaqah ini. Minta Ketua menambahkan santri.
            </x-empty>
        @else
            <form method="POST" action="{{ route('ops.attendance.update', $session) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <x-input-error :messages="$errors->get('rows')" class="mb-2" />

                @foreach ($session->attendances->sortBy('santri.user.name') as $row)
                    <div class="ui-card p-4 space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-teal-50 font-semibold text-teal-800">
                                {{ mb_substr($row->santri->user->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-semibold text-teal-950">{{ $row->santri->user->name }}</p>
                                <p class="text-sm text-slate-500">NIS {{ $row->santri->nis }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach ($statuses as $status)
                                <label class="block">
                                    <input type="radio"
                                           name="rows[{{ $row->santri_id }}][status]"
                                           value="{{ $status->value }}"
                                           class="peer sr-only"
                                           @checked(old('rows.'.$row->santri_id.'.status', $row->status->value) === $status->value)>
                                    <span class="ui-choice {{ $status->buttonClass() }}">
                                        {{ $status->label() }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <input type="text"
                               name="rows[{{ $row->santri_id }}][note]"
                               value="{{ old('rows.'.$row->santri_id.'.note', $row->note) }}"
                               placeholder="Catatan (opsional)"
                               class="ui-input">
                    </div>
                @endforeach

                <button type="submit" class="btn-primary btn-block min-h-14 text-base">
                    Simpan absensi, lanjut setoran
                </button>
            </form>

            <p class="mt-3 text-center">
                <a href="{{ route('ops.setoran.create', ['sesi' => $session]) }}" class="ui-link text-sm">
                    Lewati ke setoran santri hadir
                </a>
            </p>
        @endif

        <p class="mt-4 text-center">
            <a href="{{ route('ops.attendance.index') }}" class="ui-link text-sm inline-flex items-center gap-1">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke daftar sesi
            </a>
        </p>
    </div>
</x-app-layout>
