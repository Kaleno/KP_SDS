<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Keuangan</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">Kas DKM</h1>
            <p class="text-sm text-slate-500">Pemasukan SPP & pengeluaran masjid</p>
        </div>
    </x-slot>

    <div class="max-w-2xl space-y-5">
        <div class="grid grid-cols-3 gap-3">
            <div class="ui-card p-4 text-center">
                <p class="text-xs text-slate-500">Pemasukan</p>
                <p class="mt-1 font-display text-lg font-semibold text-teal-800">{{ number_format($pemasukan, 0, ',', '.') }}</p>
            </div>
            <div class="ui-card p-4 text-center">
                <p class="text-xs text-slate-500">Pengeluaran</p>
                <p class="mt-1 font-display text-lg font-semibold {{ $pengeluaran > 0 ? 'text-rose-800' : 'text-teal-950' }}">{{ number_format($pengeluaran, 0, ',', '.') }}</p>
            </div>
            <div class="ui-card p-4 text-center">
                <p class="text-xs text-slate-500">Saldo</p>
                <p class="mt-1 font-display text-lg font-semibold text-teal-950">{{ number_format($saldo, 0, ',', '.') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('ketua.finance.spp-amount') }}" class="ui-card p-5 grid gap-4">
            @csrf
            @method('PUT')
            <div>
                <p class="font-semibold text-teal-950">Pengaturan SPP</p>
                <p class="mt-1 text-sm text-slate-500">Biaya bulanan & tanggal jatuh tempo untuk semua santri aktif.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="spp_amount" value="Nominal (Rp)/bulan" />
                    <x-text-input id="spp_amount" name="spp_amount" type="number" min="1000" step="500" class="mt-1.5" :value="old('spp_amount', $sppAmount)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('spp_amount')" />
                </div>
                <div>
                    <x-input-label for="spp_due_day" value="Jatuh tempo (tanggal)" />
                    <x-text-input id="spp_due_day" name="spp_due_day" type="number" min="1" max="28" class="mt-1.5" :value="old('spp_due_day', $sppDueDay)" required />
                    <p class="mt-1 text-xs text-slate-500">Contoh: 10 = harus lunas sebelum tanggal 11 tiap bulan.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('spp_due_day')" />
                </div>
            </div>
            <x-primary-button class="justify-self-start">Simpan pengaturan SPP</x-primary-button>
        </form>

        <form method="POST" action="{{ route('ketua.finance.store') }}" class="ui-card p-5 grid gap-4">
            @csrf
            <p class="font-semibold text-teal-950">Catat manual</p>
            <div>
                <x-input-label for="type" value="Jenis" />
                <select id="type" name="type" class="ui-select mt-1.5" required>
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}" @selected(old('type') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="amount" value="Nominal (Rp)" />
                <x-text-input id="amount" name="amount" type="number" min="1" class="mt-1.5" :value="old('amount')" required />
            </div>
            <div>
                <x-input-label for="entry_date" value="Tanggal" />
                <x-text-input id="entry_date" name="entry_date" type="date" class="mt-1.5" :value="old('entry_date', now()->toDateString())" required />
            </div>
            <div>
                <x-input-label for="category" value="Kategori" />
                <x-text-input id="category" name="category" class="mt-1.5" :value="old('category')" placeholder="Infak, listrik, AT" />
            </div>
            <div>
                <x-input-label for="note" value="Catatan" />
                <x-text-input id="note" name="note" class="mt-1.5" :value="old('note')" />
            </div>
            <x-primary-button>Simpan</x-primary-button>
        </form>

        <div class="flex gap-2">
            <a href="{{ route('ketua.finance.index') }}" class="ui-filter-chip {{ ! $filterType ? 'is-active' : '' }}">Semua</a>
            @foreach ($types as $type)
                <a href="{{ route('ketua.finance.index', ['type' => $type->value]) }}" class="ui-filter-chip {{ $filterType === $type->value ? 'is-active' : '' }}">{{ $type->label() }}</a>
            @endforeach
        </div>

        <div class="space-y-2">
            @forelse ($entries as $entry)
                <div class="ui-card flex items-start justify-between gap-3 p-4">
                    <div>
                        <p class="font-medium text-teal-950">{{ $entry->category ?: $entry->source->label() }}</p>
                        <p class="text-xs text-slate-500">{{ $entry->entry_date->format('d/m/Y') }} · {{ $entry->note }}</p>
                    </div>
                    <p class="font-semibold {{ $entry->type === \App\Enums\FinanceType::Pemasukan ? 'text-teal-800' : 'text-rose-800' }}">
                        {{ $entry->type === \App\Enums\FinanceType::Pemasukan ? '+' : '-' }}{{ number_format($entry->amount, 0, ',', '.') }}
                    </p>
                </div>
            @empty
                <x-empty>Belum ada transaksi.</x-empty>
            @endforelse
        </div>

        {{ $entries->links() }}
    </div>
</x-app-layout>
