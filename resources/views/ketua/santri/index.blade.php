<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="ui-section-title">Master data</p>
                <h1 class="font-display text-2xl font-semibold text-teal-950">Santri</h1>
            </div>
            <a href="{{ route('ketua.santri.create') }}" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" /> Tambah
            </a>
        </div>
    </x-slot>

    <div
        class="ui-page !space-y-4"
        x-data="{
            open: false,
            detail: null,
            details: @js($details),
            show(id) {
                this.detail = this.details[id] ?? null;
                this.open = !!this.detail;
            },
            close() { this.open = false; this.detail = null; }
        }"
        @keydown.escape.window="close()"
    >
        <form method="GET" class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <select name="status" class="ui-select w-full sm:w-auto sm:min-w-[12rem]">
                <option value="">Semua status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($statusFilter === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <button class="btn-secondary w-full sm:w-auto">Filter</button>
        </form>

        <div class="ui-table-wrap">
            <div class="overflow-x-auto">
                <table class="ui-table ui-table-stack">
                    <thead>
                        <tr>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>Jalur</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($santriList as $santri)
                            <tr>
                                <td data-label="NIS" class="font-medium">{{ $santri->nis }}</td>
                                <td data-label="Nama">
                                    <div class="ui-table-value">
                                        <span class="font-medium text-teal-950">{{ $santri->user->name }}</span>
                                        <span class="mt-0.5 block text-xs text-slate-500">{{ $santri->gender->label() }}</span>
                                    </div>
                                </td>
                                <td data-label="Jalur">
                                    <x-badge :tone="$santri->track === \App\Enums\SantriTrack::Iqro ? 'warn' : 'ok'">
                                        {{ $santri->track?->label() ?? '—' }}
                                    </x-badge>
                                </td>
                                <td data-label="Status">
                                    <x-badge :tone="$santri->status->badgeTone()">{{ $santri->status->label() }}</x-badge>
                                </td>
                                <td data-label="" class="ui-table-actions">
                                    <div class="flex flex-wrap items-center justify-end gap-1">
                                        <button
                                            type="button"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-teal-800 hover:bg-teal-50"
                                            title="Lihat"
                                            @click="show({{ $santri->id }})"
                                        >
                                            <x-icon name="eye" class="h-4 w-4" />
                                        </button>
                                        <a
                                            href="{{ route('ketua.santri.edit', $santri) }}"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-teal-800 hover:bg-teal-50"
                                            title="Ubah"
                                        >
                                            <x-icon name="pencil" class="h-4 w-4" />
                                        </a>
                                        <form
                                            method="POST"
                                            action="{{ route('ketua.santri.reset-password', $santri) }}"
                                            onsubmit="return confirm('Reset password {{ $santri->user->name }} ke default (password)?')"
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-amber-700 hover:bg-amber-50"
                                                title="Reset password"
                                            >
                                                <x-icon name="key" class="h-4 w-4" />
                                            </button>
                                        </form>
                                        <form
                                            method="POST"
                                            action="{{ route('ketua.santri.destroy', $santri) }}"
                                            onsubmit="return confirm('Hapus permanen {{ $santri->user->name }} (NIS {{ $santri->nis }})? Data absensi, setoran, dan SPP ikut terhapus.')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-rose-700 hover:bg-rose-50"
                                                title="Hapus"
                                            >
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Belum ada santri.</td></tr>
                        @endempty
                    </tbody>
                </table>
            </div>
        </div>

        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-label="Detail santri"
        >
            <div class="absolute inset-0 bg-teal-950/50" @click="close()"></div>
            <div class="relative max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white p-5 shadow-lift sm:p-7">
                <div class="mb-5 flex items-start justify-between gap-3">
                    <h2 class="font-display text-xl font-semibold text-teal-950">Detail Santri</h2>
                    <button type="button" class="rounded-xl p-2 text-slate-500 hover:bg-slate-100" @click="close()" aria-label="Tutup">
                        <span class="text-lg leading-none">&times;</span>
                    </button>
                </div>

                <template x-if="detail">
                    <div class="grid gap-6 sm:grid-cols-2 sm:gap-8">
                        <div class="space-y-4">
                            <div class="flex flex-col items-start gap-2">
                                <template x-if="detail.photo_url">
                                    <img :src="detail.photo_url" alt="" class="h-24 w-24 rounded-full object-cover">
                                </template>
                                <template x-if="!detail.photo_url">
                                    <div class="flex h-24 w-24 items-center justify-center rounded-full bg-teal-50 font-display text-2xl font-semibold text-teal-800" x-text="(detail.name || '?').charAt(0)"></div>
                                </template>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Foto</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Nama</p>
                                <p class="mt-1 font-semibold text-teal-950" x-text="detail.name"></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Tanggal lahir</p>
                                <p class="mt-1 text-slate-800" x-text="detail.birth_date"></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Telepon</p>
                                <p class="mt-1 text-slate-800" x-text="detail.phone"></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Alamat</p>
                                <p class="mt-1 text-slate-800" x-text="detail.address"></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Wali</p>
                                <p class="mt-1 text-slate-800" x-text="detail.parent_name"></p>
                            </div>
                        </div>

                        <div class="space-y-4 border-t border-slate-100 pt-5 sm:border-l sm:border-t-0 sm:pl-8 sm:pt-0">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Data lainnya</p>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Jalur mengaji</p>
                                <p class="mt-1 text-slate-800" x-text="detail.track"></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Status</p>
                                <p class="mt-1 text-slate-800" x-text="detail.status"></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Tanggal bergabung</p>
                                <p class="mt-1 text-slate-800" x-text="detail.joined_at"></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Durasi bergabung</p>
                                <p class="mt-1 text-slate-800" x-text="detail.duration"></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Tanggal lulus</p>
                                <p class="mt-1 text-slate-800" x-text="detail.graduated_at"></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-app-layout>
