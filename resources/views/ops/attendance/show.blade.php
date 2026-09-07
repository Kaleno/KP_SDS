<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Absensi</p>
            <h1 class="font-display text-xl font-semibold text-teal-950 sm:text-2xl">{{ $session->schedule->halaqah->name }}</h1>
            <p class="text-sm text-slate-500">
                {{ $session->session_date->format('d/m/Y') }} · {{ $session->schedule->timeRange() }}
                · {{ $session->schedule->location->name }}
            </p>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        @if ($session->attendances->isEmpty())
            <x-empty>
                Belum ada anggota aktif di halaqah ini. Minta Ketua menambahkan santri.
            </x-empty>
        @else
            @php
                $roster = $session->attendances->sortBy('santri.user.name')->values();
                $statusMap = [];
                $notesOpen = [];
                foreach ($roster as $row) {
                    $id = (string) $row->santri_id;
                    $currentStatus = old('rows.'.$row->santri_id.'.status', $row->status->value);
                    $currentNote = old('rows.'.$row->santri_id.'.note', $row->note);
                    $statusMap[$id] = $currentStatus;
                    $notesOpen[$id] = filled($currentNote) || $currentStatus !== \App\Enums\AttendanceStatus::Hadir->value;
                }
            @endphp

            <form method="POST"
                  action="{{ route('ops.attendance.update', $session) }}"
                  class="space-y-3"
                  x-data="{
                      status: {{ \Illuminate\Support\Js::from($statusMap) }},
                      notesOpen: {{ \Illuminate\Support\Js::from($notesOpen) }},
                      count(code) {
                          return Object.values(this.status).filter((value) => value === code).length
                      },
                  }">
                @csrf
                @method('PUT')
                <x-input-error :messages="$errors->get('rows')" class="mb-2" />

                <div class="ui-card divide-y divide-slate-100/90 overflow-hidden">
                    @foreach ($roster as $row)
                        @php $id = (string) $row->santri_id; @endphp
                        <div class="px-3 py-2.5 sm:px-4">
                            <div class="flex w-full min-w-0 flex-col gap-2.5 lg:flex-row lg:items-center lg:gap-4">
                                <div class="flex min-w-0 items-center gap-2.5 lg:w-52 lg:shrink-0">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-teal-50 text-sm font-semibold text-teal-800">
                                        {{ mb_substr($row->santri->user->name, 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-teal-950">{{ $row->santri->user->name }}</p>
                                        <p class="text-xs text-slate-500">NIS {{ $row->santri->nis }}</p>
                                    </div>
                                </div>

                                <div class="attendance-picks lg:flex-1">
                                    @foreach ($statuses as $status)
                                        <label class="block min-w-0 cursor-pointer">
                                            <input type="radio"
                                                   name="rows[{{ $row->santri_id }}][status]"
                                                   value="{{ $status->value }}"
                                                   class="peer sr-only"
                                                   x-model="status['{{ $id }}']"
                                                   @checked($statusMap[$id] === $status->value)>
                                            <span class="ui-choice ui-choice-compact {{ $status->buttonClass() }}">
                                                {{ $status->label() }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <button type="button"
                                    class="mt-2 text-xs font-semibold text-teal-800"
                                    x-cloak
                                    x-show="status['{{ $id }}'] === 'hadir' && ! notesOpen['{{ $id }}']"
                                    @click="notesOpen['{{ $id }}'] = true">
                                + Catatan
                            </button>

                            <div class="mt-2" x-show="status['{{ $id }}'] !== 'hadir' || notesOpen['{{ $id }}']" x-cloak>
                                <input type="text"
                                       name="rows[{{ $row->santri_id }}][note]"
                                       value="{{ old('rows.'.$row->santri_id.'.note', $row->note) }}"
                                       placeholder="Catatan (opsional)"
                                       class="ui-input min-h-11">
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="sticky bottom-20 z-20 lg:static lg:bottom-auto">
                    <div class="rounded-2xl border border-teal-950/5 bg-cream-50/95 p-3 shadow-soft backdrop-blur lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
                        <p class="mb-2 text-center text-xs text-slate-500 lg:mb-3">
                            <span x-text="count('hadir')"></span> hadir
                            · <span x-text="count('izin')"></span> izin
                            · <span x-text="count('sakit')"></span> sakit
                            · <span x-text="count('alfa')"></span> alfa
                        </p>
                        <button type="submit" class="btn-primary btn-block min-h-12 text-base">
                            Simpan absensi, lanjut setoran
                        </button>
                    </div>
                </div>
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
