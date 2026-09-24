<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="ui-section-title">Master data</p>
                <h1 class="font-display text-2xl font-semibold text-teal-950">Pengajar</h1>
            </div>
            <a href="{{ route('ketua.ustaz.create') }}" class="btn-primary">
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
        <div class="ui-table-wrap">
            <div class="overflow-x-auto">
                <table class="ui-table ui-table-stack">
                    <thead>
                        <tr>
                            <th>NIP</th>
                            <th>Nama</th>
                            <th>Peran</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ustazList as $ustaz)
                            <tr>
                                <td data-label="NIP" class="font-medium">{{ $ustaz->nip ?: '—' }}</td>
                                <td data-label="Nama">
                                    <div class="ui-table-value">
                                        <span class="font-medium text-teal-950">{{ $ustaz->name }}</span>
                                        <span class="mt-0.5 block text-xs text-slate-500">{{ $ustaz->username }}</span>
                                    </div>
                                </td>
                                <td data-label="Peran">{{ \App\Support\Role::label($ustaz->getRoleNames()->first() ?? '') }}</td>
                                <td data-label="Status">
                                    <x-badge :tone="$ustaz->is_active ? 'ok' : 'muted'">{{ $ustaz->is_active ? 'Aktif' : 'Tidak aktif' }}</x-badge>
                                </td>
                                <td data-label="" class="ui-table-actions">
                                    <div class="flex flex-wrap items-center justify-end gap-1">
                                        <button
                                            type="button"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-teal-800 hover:bg-teal-50"
                                            title="Lihat"
                                            @click="show({{ $ustaz->id }})"
                                        >
                                            <x-icon name="eye" class="h-4 w-4" />
                                        </button>
                                        <a
                                            href="{{ route('ketua.ustaz.edit', $ustaz) }}"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-teal-800 hover:bg-teal-50"
                                            title="Ubah"
                                        >
                                            <x-icon name="pencil" class="h-4 w-4" />
                                        </a>
                                        <form
                                            method="POST"
                                            action="{{ route('ketua.ustaz.reset-password', $ustaz) }}"
                                            onsubmit="return confirm('Reset password {{ $ustaz->name }} ke default (password)?')"
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
                                        <form method="POST" action="{{ route('ketua.ustaz.toggle', $ustaz) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="inline-flex h-9 items-center rounded-xl px-2 text-xs font-semibold text-slate-600 hover:bg-slate-100"
                                                title="{{ $ustaz->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                            >
                                                {{ $ustaz->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <form
                                            method="POST"
                                            action="{{ route('ketua.ustaz.destroy', $ustaz) }}"
                                            onsubmit="return confirm('Hapus ustadz {{ $ustaz->name }} dari daftar? Riwayat setoran tetap tersimpan.')"
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
                            <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Belum ada ustadz.</td></tr>
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
            aria-label="Detail ustadz"
        >
            <div class="absolute inset-0 bg-teal-950/50" @click="close()"></div>
            <div class="relative max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white p-5 shadow-lift sm:p-7">
                <div class="mb-5 flex items-start justify-between gap-3">
                    <h2 class="font-display text-xl font-semibold text-teal-950">Detail Ustadz</h2>
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
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">NIP</p>
                                <p class="mt-1 text-slate-800" x-text="detail.nip"></p>
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
                        </div>

                        <div class="space-y-4 border-t border-slate-100 pt-5 sm:border-l sm:border-t-0 sm:pl-8 sm:pt-0">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Data lainnya</p>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Jenjang pendidikan</p>
                                <p class="mt-1 text-slate-800" x-text="detail.education_level"></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Peran</p>
                                <p class="mt-1 text-slate-800" x-text="detail.role"></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Status</p>
                                <p class="mt-1 text-slate-800" x-text="detail.status"></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Username</p>
                                <p class="mt-1 text-slate-800" x-text="detail.username"></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-app-layout>
