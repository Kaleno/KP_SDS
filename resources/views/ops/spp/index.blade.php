<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-section-title">Pembayaran</p>
            <h1 class="font-display text-2xl font-semibold text-teal-950">SPP & tunggakan</h1>
            <p class="text-sm text-slate-500">
                Rp {{ number_format($amount, 0, ',', '.') }}/bulan · jatuh tempo tanggal {{ $dueDay }}
            </p>
        </div>
    </x-slot>

    <div class="max-w-xl space-y-5">
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            <div class="ui-card p-4">
                <p class="text-xs text-slate-500">Lunas bulan ini</p>
                <p class="mt-1 font-display text-2xl font-semibold text-teal-800">
                    {{ $summary['paidThisMonth'] }}/{{ $summary['paidThisMonth'] + $summary['unpaidThisMonth'] }}
                </p>
            </div>
            <div class="ui-card p-4">
                <p class="text-xs text-slate-500">Belum bayar</p>
                <p class="mt-1 font-display text-2xl font-semibold {{ $summary['unpaidThisMonth'] > 0 ? 'text-amber-800' : 'text-teal-950' }}">
                    {{ $summary['unpaidThisMonth'] }}
                </p>
                @if ($summary['unpaidThisMonth'] > 0)
                    <p class="mt-1 text-xs text-slate-500">Rp {{ number_format($summary['unpaidThisMonthAmount'], 0, ',', '.') }}</p>
                @endif
            </div>
            <a href="{{ route('ops.spp.index', ['year' => $year, 'month' => $month, 'filter' => 'nunggak']) }}"
               class="ui-card p-4 {{ $filter === 'nunggak' ? 'ring-2 ring-rose-300' : '' }}">
                <p class="text-xs text-slate-500">Nunggak &gt;1 bulan</p>
                <p class="mt-1 font-display text-2xl font-semibold {{ $summary['deepArrears'] > 0 ? 'text-rose-800' : 'text-teal-950' }}">
                    {{ $summary['deepArrears'] }}
                </p>
                <p class="mt-1 text-xs text-teal-800">Lihat daftar</p>
            </a>
        </div>

        <form method="GET" class="flex flex-wrap gap-2">
            <select name="month" class="ui-select flex-1 min-w-[8rem]">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" @selected($month === $m)>{{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}</option>
                @endfor
            </select>
            <x-text-input type="number" name="year" class="w-28" :value="$year" />
            <input type="hidden" name="filter" value="{{ $filter }}">
            <button class="btn-secondary">Lihat</button>
            @if ($filter === 'nunggak')
                <a href="{{ route('ops.spp.index', ['year' => $year, 'month' => $month]) }}" class="btn-ghost">Semua tunggakan</a>
            @endif
        </form>

        <form method="POST"
              action="{{ route('ops.spp.store') }}"
              class="ui-card p-5 grid gap-4"
              x-data="{
                  amount: {{ (int) $amount }},
                  fromYear: {{ (int) old('from_year', $year) }},
                  fromMonth: {{ (int) old('from_month', $month) }},
                  toYear: {{ (int) old('to_year', $year) }},
                  toMonth: {{ (int) old('to_month', $month) }},
                  months() {
                      let from = this.fromYear * 12 + this.fromMonth;
                      let to = this.toYear * 12 + this.toMonth;
                      if (from > to) { const t = from; from = to; to = t; }
                      return Math.max(1, to - from + 1);
                  },
                  total() { return this.months() * this.amount; }
              }">
            @csrf
            <p class="font-semibold text-teal-950">Catat pembayaran</p>
            <div>
                <x-input-label for="santri_id" value="Santri" />
                <select id="santri_id" name="santri_id" class="ui-select mt-1.5" required>
                    <option value="">Pilih santri</option>
                    @foreach ($santriOptions as $santri)
                        <option value="{{ $santri->id }}" @selected(old('santri_id') == $santri->id)>{{ $santri->user->name }} ({{ $santri->nis }})</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('santri_id')" />
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <x-input-label for="from_month" value="Dari bulan" />
                    <div class="mt-1.5 flex gap-2">
                        <select id="from_month" name="from_month" class="ui-select flex-1" x-model.number="fromMonth" required>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}</option>
                            @endfor
                        </select>
                        <x-text-input type="number" name="from_year" class="w-24" x-model.number="fromYear" required />
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('from_month')" />
                    <x-input-error class="mt-2" :messages="$errors->get('from_year')" />
                </div>
                <div>
                    <x-input-label for="to_month" value="Sampai bulan" />
                    <div class="mt-1.5 flex gap-2">
                        <select id="to_month" name="to_month" class="ui-select flex-1" x-model.number="toMonth" required>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}</option>
                            @endfor
                        </select>
                        <x-text-input type="number" name="to_year" class="w-24" x-model.number="toYear" required />
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('to_month')" />
                    <x-input-error class="mt-2" :messages="$errors->get('to_year')" />
                </div>
            </div>
            <div class="rounded-2xl bg-cream-100 px-4 py-3 text-sm text-slate-700">
                Preview:
                <span class="font-semibold text-teal-950" x-text="months() + ' bulan × Rp ' + amount.toLocaleString('id-ID') + ' = Rp ' + total().toLocaleString('id-ID')"></span>
            </div>
            <div>
                <x-input-label for="paid_at" value="Tanggal bayar" />
                <x-text-input id="paid_at" name="paid_at" type="date" class="mt-1.5" :value="old('paid_at', now()->toDateString())" required />
                <x-input-error class="mt-2" :messages="$errors->get('paid_at')" />
            </div>
            <div>
                <x-input-label for="note" value="Catatan" />
                <x-text-input id="note" name="note" class="mt-1.5" :value="old('note')" />
            </div>
            <x-input-error :messages="$errors->get('periods')" />
            <x-primary-button class="btn-block min-h-12">Simpan bayar</x-primary-button>
        </form>

        <section class="space-y-3">
            <h2 class="ui-section-title">
                @if ($filter === 'nunggak')
                    Nunggak &gt;1 bulan ({{ $tunggakan->count() }})
                @else
                    Menunggak ({{ $tunggakan->count() }})
                @endif
            </h2>
            @forelse ($tunggakan as $santri)
                <div class="ui-card flex items-start justify-between gap-3 p-4">
                    <div>
                        <p class="font-medium text-teal-950">{{ $santri->user->name }}</p>
                        <p class="text-xs text-slate-500">NIS {{ $santri->nis }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $santri->unpaid_labels }}</p>
                    </div>
                    <div class="text-right">
                        <x-badge :tone="(int) $santri->unpaid_count > 1 ? 'danger' : 'warn'">
                            {{ $santri->unpaid_count }} bln
                        </x-badge>
                        <p class="mt-1 text-xs text-slate-500">Rp {{ number_format($santri->unpaid_amount, 0, ',', '.') }}</p>
                    </div>
                </div>
            @empty
                <x-empty>
                    @if ($filter === 'nunggak')
                        Tidak ada santri nunggak lebih dari 1 bulan.
                    @else
                        Semua santri aktif sudah bayar kewajiban sampai bulan ini.
                    @endif
                </x-empty>
            @endforelse
        </section>
    </div>
</x-app-layout>
