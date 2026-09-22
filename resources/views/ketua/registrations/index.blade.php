<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Pendaftaran</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Antrian santri baru</h1>
            <p class="text-sm text-slate-500">Setujui atau tolak pendaftaran dari antrian ini</p>
        </div>
    </x-slot>

    <div class="max-w-3xl space-y-6">
        <section class="space-y-3">
            <h2 class="ui-section-title">Menunggu ({{ $pending->count() }})</h2>
            @forelse ($pending as $item)
                <a href="{{ route('ketua.registrations.show', $item) }}" class="ui-card flex items-center gap-4 p-4">
                    @if ($item->photoUrl())
                        <img src="{{ $item->photoUrl() }}" alt="" class="h-14 w-14 rounded-2xl object-cover">
                    @else
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-teal-50 text-teal-800 font-semibold">
                            {{ mb_substr($item->name, 0, 1) }}
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-teal-950">{{ $item->name }}</p>
                        <p class="text-sm text-slate-500">{{ $item->track->label() }} · {{ $item->school_level->label() }} · {{ $item->parent_name }}</p>
                    </div>
                    <x-badge tone="warn">Menunggu</x-badge>
                </a>
            @empty
                <x-empty>Tidak ada pendaftaran menunggu.</x-empty>
            @endforelse
        </section>

        @if ($recent->isNotEmpty())
            <section class="space-y-3">
                <h2 class="ui-section-title">Baru diproses</h2>
                @foreach ($recent as $item)
                    <div class="ui-card flex items-center justify-between gap-3 p-4">
                        <div>
                            <p class="font-medium text-teal-950">{{ $item->name }}</p>
                            <p class="text-xs text-slate-500">{{ $item->reviewed_at?->format('d/m/Y H:i') }}</p>
                        </div>
                        <x-badge :tone="$item->status === \App\Enums\RegistrationStatus::Approved ? 'ok' : 'danger'">
                            {{ $item->status->label() }}
                        </x-badge>
                    </div>
                @endforeach
            </section>
        @endif
    </div>
</x-app-layout>
