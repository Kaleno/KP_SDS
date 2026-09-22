<?php

namespace App\Http\Controllers\Ketua;

use App\Enums\FinanceSource;
use App\Enums\FinanceType;
use App\Http\Requests\Ketua\StoreFinanceEntryRequest;
use App\Http\Requests\Ketua\UpdateSppAmountRequest;
use App\Models\FinanceEntry;
use App\Support\AppSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceController extends KetuaController
{
    public function index(Request $request): View
    {
        $entries = FinanceEntry::query()
            ->with('creator')
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->input('type')))
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $pemasukan = (int) FinanceEntry::query()->where('type', FinanceType::Pemasukan)->sum('amount');
        $pengeluaran = (int) FinanceEntry::query()->where('type', FinanceType::Pengeluaran)->sum('amount');

        return view('ketua.finance.index', [
            'entries' => $entries,
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'saldo' => $pemasukan - $pengeluaran,
            'types' => FinanceType::cases(),
            'filterType' => $request->input('type'),
            'sppAmount' => AppSettings::sppMonthlyAmount(),
            'sppDueDay' => AppSettings::sppDueDay(),
        ]);
    }

    public function store(StoreFinanceEntryRequest $request): RedirectResponse
    {
        FinanceEntry::query()->create([
            ...$request->safe()->only(['type', 'amount', 'entry_date', 'category', 'note']),
            'source' => FinanceSource::Manual,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('ketua.finance.index')->with('status', 'Entri keuangan disimpan.');
    }

    public function updateSppAmount(UpdateSppAmountRequest $request): RedirectResponse
    {
        AppSettings::setSppMonthlyAmount($request->integer('spp_amount'));
        AppSettings::setSppDueDay($request->integer('spp_due_day'));

        return redirect()
            ->route('ketua.finance.index')
            ->with(
                'status',
                'Pengaturan SPP disimpan: Rp '.number_format($request->integer('spp_amount'), 0, ',', '.')
                .'/bulan, jatuh tempo tanggal '.$request->integer('spp_due_day').'.'
            );
    }
}
