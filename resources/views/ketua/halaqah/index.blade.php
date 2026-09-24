<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="ui-section-title">Pembelajaran</p>
                <h1 class="font-display text-2xl font-semibold text-teal-950">Kelas</h1>
            </div>
            <a href="{{ route('ketua.halaqah.create') }}" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" /> Buat kelas
            </a>
        </div>
    </x-slot>

    <div class="ui-page">
        <div class="ui-table-wrap">
            <div class="overflow-x-auto">
                <table class="ui-table ui-table-stack">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Pengajar</th>
                            <th>Jadwal</th>
                            <th>Santri</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kelasList as $item)
                            <tr>
                                <td data-label="Nama" class="font-medium">{{ $item->name }}</td>
                                <td data-label="Pengajar">{{ $item->ustaz->name }}</td>
                                <td data-label="Jadwal">
                                    @if ($item->schedules->isEmpty())
                                        <span class="text-slate-400">Belum ada jadwal</span>
                                    @else
                                        {{ \App\Support\WeekDay::summarize($item->schedules->pluck('day_of_week')->all()) }}
                                        · {{ $item->schedules->first()->timeRange() }}
                                    @endif
                                </td>
                                <td data-label="Santri">{{ $item->active_members_count }}</td>
                                <td data-label="" class="ui-table-actions">
                                    <a href="{{ route('ketua.halaqah.edit', $item) }}" class="ui-link">Ubah</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Belum ada kelas. Buat kelas untuk mengatur jadwal pembelajaran.</td></tr>
                        @endempty
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
