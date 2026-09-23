<?php

namespace App\Http\Controllers\Ops;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ops\StoreSppPaymentRequest;
use App\Models\SantriProfile;
use App\Services\SppService;
use App\Support\OperationalAccess;
use App\Support\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class SppController extends Controller
{
    public function __construct(
        private SppService $spp,
        private OperationalAccess $access,
    ) {}

    public function index(Request $request): View
    {
        $this->assertLeader($request);

        $historyYear = (int) $request->input('year', now()->year);
        $historyMonth = (int) $request->input('month', now()->month);
        $filter = $request->input('filter', 'bulan_ini');

        $tunggakan = $this->spp->tunggakan();
        if ($filter === 'nunggak') {
            $tunggakan = $tunggakan->filter(
                fn (SantriProfile $santri): bool => (int) $santri->getAttribute('unpaid_count') > 1
            )->values();
        }

        return view('ops.spp.index', [
            'tunggakan' => $tunggakan,
            'year' => $historyYear,
            'month' => $historyMonth,
            'filter' => $filter,
            'amount' => $this->spp->monthlyAmount(),
            'dueDay' => $this->spp->dueDay(),
            'summary' => $this->spp->summary(),
            'history' => $this->spp->recentPaymentBatches($historyYear, $historyMonth),
            'santriOptions' => SantriProfile::query()->aktif()->with('user')->orderBy('nis')->get(),
            'isKetua' => $request->user()->hasRole(Role::Ketua),
        ]);
    }

    public function store(StoreSppPaymentRequest $request): RedirectResponse
    {
        $this->assertLeader($request);

        $santri = SantriProfile::query()->findOrFail($request->integer('santri_id'));
        $this->access->assertSantri($request->user(), $santri);

        $periods = $this->spp->expandRange(
            $request->integer('from_year'),
            $request->integer('from_month'),
            $request->integer('to_year'),
            $request->integer('to_month'),
        );

        $paidAt = $request->string('paid_at')->toString();
        $paidAtDate = Carbon::parse($paidAt);

        $payments = $this->spp->recordPeriods(
            $santri,
            $request->user(),
            $periods,
            $paidAt,
            $request->input('note'),
        );

        $count = $payments->count();
        $total = $count * $this->spp->monthlyAmount();

        return redirect()
            ->route('ops.spp.index', [
                'year' => $paidAtDate->year,
                'month' => $paidAtDate->month,
            ])
            ->with(
                'status',
                $count.' bulan SPP dicatat (Rp '.number_format($total, 0, ',', '.').').'
            );
    }

    private function assertLeader(Request $request): void
    {
        abort_unless(
            $request->user()->hasRole(Role::Ketua) || $request->user()->hasRole(Role::KetuaPengajar),
            403,
        );
    }
}
