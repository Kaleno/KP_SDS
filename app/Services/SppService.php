<?php

namespace App\Services;

use App\Enums\SantriStatus;
use App\Models\SantriProfile;
use App\Models\SppPayment;
use App\Models\User;
use App\Support\AppSettings;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SppService
{
    /** @deprecated Use monthlyAmount() — kept for older tests/call sites. */
    public const MONTHLY_AMOUNT = AppSettings::DefaultSppMonthlyAmount;

    public function monthlyAmount(): int
    {
        return AppSettings::sppMonthlyAmount();
    }

    public function dueDay(): int
    {
        return AppSettings::sppDueDay();
    }

    /**
     * @param  array{year: int, month: int, paid_at: string, note?: string|null}  $data
     */
    public function record(SantriProfile $santri, User $recorder, array $data): SppPayment
    {
        $payments = $this->recordPeriods($santri, $recorder, [[
            'year' => (int) $data['year'],
            'month' => (int) $data['month'],
        ]], $data['paid_at'], $data['note'] ?? null);

        return $payments->first();
    }

    /**
     * @param  list<array{year: int, month: int}>  $periods
     * @return Collection<int, SppPayment>
     */
    public function recordPeriods(
        SantriProfile $santri,
        User $recorder,
        array $periods,
        string $paidAt,
        ?string $note = null,
    ): Collection {
        $periods = $this->normalizePeriods($periods);

        if ($periods === []) {
            throw ValidationException::withMessages([
                'periods' => 'Pilih minimal satu bulan yang dilunasi.',
            ]);
        }

        foreach ($periods as $period) {
            $exists = SppPayment::query()
                ->where('santri_id', $santri->id)
                ->where('year', $period['year'])
                ->where('month', $period['month'])
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'periods' => 'SPP '.$this->periodLabel($period['year'], $period['month']).' sudah dicatat untuk santri tersebut.',
                ]);
            }
        }

        $amountPerMonth = $this->monthlyAmount();
        $batchId = (string) Str::uuid();

        return DB::transaction(function () use ($santri, $recorder, $periods, $paidAt, $note, $amountPerMonth, $batchId) {
            $payments = collect();

            foreach ($periods as $period) {
                $payments->push(SppPayment::query()->create([
                    'batch_id' => $batchId,
                    'santri_id' => $santri->id,
                    'year' => $period['year'],
                    'month' => $period['month'],
                    'amount' => $amountPerMonth,
                    'paid_at' => $paidAt,
                    'recorded_by' => $recorder->id,
                    'note' => $note,
                ]));
            }

            return $payments;
        });
    }

    /**
     * Months the santri must pay from activation month through the current month.
     *
     * @return list<array{year: int, month: int, label: string, key: string}>
     */
    public function obligatedPeriods(SantriProfile $santri, ?Carbon $through = null): array
    {
        $through ??= now()->startOfMonth();
        $start = $this->obligationStart($santri);

        if ($start->greaterThan($through->copy()->startOfMonth())) {
            return [];
        }

        $cursor = $start->copy()->startOfMonth();
        $end = $through->copy()->startOfMonth();
        $rows = [];

        while ($cursor->lte($end)) {
            $year = (int) $cursor->year;
            $month = (int) $cursor->month;
            $rows[] = [
                'year' => $year,
                'month' => $month,
                'label' => $cursor->translatedFormat('F Y'),
                'key' => sprintf('%04d-%02d', $year, $month),
            ];
            $cursor->addMonth();
        }

        return $rows;
    }

    /**
     * @return list<array{year: int, month: int, label: string, key: string}>
     */
    public function unpaidPeriods(SantriProfile $santri, ?Carbon $through = null): array
    {
        $paid = SppPayment::query()
            ->where('santri_id', $santri->id)
            ->get(['year', 'month'])
            ->map(fn (SppPayment $p): string => sprintf('%04d-%02d', $p->year, $p->month))
            ->all();

        return array_values(array_filter(
            $this->obligatedPeriods($santri, $through),
            fn (array $period): bool => ! in_array($period['key'], $paid, true),
        ));
    }

    /**
     * @return Collection<int, SantriProfile>
     */
    public function tunggakan(?int $year = null, ?int $month = null): Collection
    {
        $year ??= (int) now()->year;
        $month ??= (int) now()->month;
        $through = Carbon::create($year, $month, 1)->startOfMonth();

        return SantriProfile::query()
            ->with('user')
            ->where('status', SantriStatus::Aktif)
            ->orderBy('nis')
            ->get()
            ->filter(function (SantriProfile $santri) use ($through): bool {
                return $this->unpaidPeriods($santri, $through) !== [];
            })
            ->values()
            ->each(function (SantriProfile $santri) use ($through): void {
                $unpaid = $this->unpaidPeriods($santri, $through);
                $santri->setAttribute('unpaid_count', count($unpaid));
                $santri->setAttribute('unpaid_labels', collect($unpaid)->pluck('label')->implode(', '));
                $santri->setAttribute('unpaid_amount', count($unpaid) * $this->monthlyAmount());
            });
    }

    /**
     * Santri with more than one unpaid obligated month through current month.
     *
     * @return Collection<int, SantriProfile>
     */
    public function deepArrears(): Collection
    {
        return $this->tunggakan()
            ->filter(fn (SantriProfile $santri): bool => (int) $santri->getAttribute('unpaid_count') > 1)
            ->values();
    }

    /**
     * @return array{
     *     amount: int,
     *     dueDay: int,
     *     aktif: int,
     *     paidThisMonth: int,
     *     unpaidThisMonth: int,
     *     unpaidThisMonthAmount: int,
     *     deepArrears: int,
     *     overdue: bool
     * }
     */
    public function summary(?int $year = null, ?int $month = null): array
    {
        $year ??= (int) now()->year;
        $month ??= (int) now()->month;
        $amount = $this->monthlyAmount();
        $aktif = SantriProfile::query()->aktif()->count();

        $paidIds = SppPayment::query()
            ->where('year', $year)
            ->where('month', $month)
            ->pluck('santri_id');

        $obligatedThisMonth = SantriProfile::query()
            ->aktif()
            ->get()
            ->filter(function (SantriProfile $santri) use ($year, $month): bool {
                $start = $this->obligationStart($santri);

                return $start->lte(Carbon::create($year, $month, 1)->endOfMonth());
            });

        $paidThisMonth = $obligatedThisMonth->filter(
            fn (SantriProfile $santri): bool => $paidIds->contains($santri->id)
        )->count();

        $unpaidThisMonth = $obligatedThisMonth->count() - $paidThisMonth;
        $deep = $this->deepArrears()->count();
        $dueDay = $this->dueDay();
        $overdue = now()->day > $dueDay && $unpaidThisMonth > 0;

        return [
            'amount' => $amount,
            'dueDay' => $dueDay,
            'aktif' => $aktif,
            'paidThisMonth' => $paidThisMonth,
            'unpaidThisMonth' => max(0, $unpaidThisMonth),
            'unpaidThisMonthAmount' => max(0, $unpaidThisMonth) * $amount,
            'deepArrears' => $deep,
            'overdue' => $overdue,
        ];
    }

    /**
     * Payment batches for the SPP ops screen (one row per form submit), filtered by payment date month.
     *
     * @return Collection<int, array{paid_at: Carbon, santri: SantriProfile, months: int, total: int, note: ?string, periods: string}>
     */
    public function recentPaymentBatches(int $year, int $month, int $limit = 100): Collection
    {
        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth();

        $batches = SppPayment::query()
            ->selectRaw('batch_id, santri_id, paid_at, SUM(amount) as total, COUNT(*) as months, MAX(note) as note, MAX(id) as latest_id')
            ->whereDate('paid_at', '>=', $from->toDateString())
            ->whereDate('paid_at', '<=', $to->toDateString())
            ->groupBy('batch_id', 'santri_id', 'paid_at')
            ->orderByDesc('paid_at')
            ->orderByDesc('latest_id')
            ->limit($limit)
            ->get();

        if ($batches->isEmpty()) {
            return collect();
        }

        $santris = SantriProfile::query()
            ->with('user')
            ->whereIn('id', $batches->pluck('santri_id')->unique())
            ->get()
            ->keyBy('id');

        $periodLabels = SppPayment::query()
            ->whereIn('batch_id', $batches->pluck('batch_id'))
            ->orderBy('year')
            ->orderBy('month')
            ->get(['batch_id', 'year', 'month'])
            ->groupBy('batch_id')
            ->map(fn (Collection $rows): string => $rows
                ->map(fn (SppPayment $row): string => sprintf('%02d/%d', $row->month, $row->year))
                ->implode(', '));

        return $batches->map(function (SppPayment $batch) use ($santris, $periodLabels): array {
            return [
                'paid_at' => Carbon::parse($batch->paid_at)->startOfDay(),
                'santri' => $santris->get($batch->santri_id),
                'months' => (int) $batch->months,
                'total' => (int) $batch->total,
                'note' => $batch->note,
                'periods' => $periodLabels->get($batch->batch_id, ''),
            ];
        })->values();
    }

    /**
     * @return list<array{year: int, month: int, label: string, paid: bool, payment: ?SppPayment, obligated: bool}>
     */
    public function historyFor(SantriProfile $santri, int $months = 6): array
    {
        $cursor = now()->startOfMonth();
        $rows = [];
        $start = $this->obligationStart($santri);

        for ($i = 0; $i < $months; $i++) {
            $year = (int) $cursor->year;
            $month = (int) $cursor->month;
            $obligated = $cursor->copy()->startOfMonth()->gte($start);
            $payment = SppPayment::query()
                ->where('santri_id', $santri->id)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            $rows[] = [
                'year' => $year,
                'month' => $month,
                'label' => $cursor->translatedFormat('F Y'),
                'paid' => $payment !== null,
                'payment' => $payment,
                'obligated' => $obligated,
            ];

            $cursor->subMonth();
        }

        return $rows;
    }

    public function unpaidMonthCount(SantriProfile $santri): int
    {
        return count($this->unpaidPeriods($santri));
    }

    public function obligationStart(SantriProfile $santri): Carbon
    {
        return ($santri->created_at ?? now())->copy()->startOfMonth();
    }

    public function periodLabel(int $year, int $month): string
    {
        return sprintf('%02d/%d', $month, $year);
    }

    /**
     * Expand from–to inclusive into period list.
     *
     * @return list<array{year: int, month: int}>
     */
    public function expandRange(int $fromYear, int $fromMonth, int $toYear, int $toMonth): array
    {
        $from = Carbon::create($fromYear, $fromMonth, 1)->startOfMonth();
        $to = Carbon::create($toYear, $toMonth, 1)->startOfMonth();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        $periods = [];
        $cursor = $from->copy();

        while ($cursor->lte($to)) {
            $periods[] = [
                'year' => (int) $cursor->year,
                'month' => (int) $cursor->month,
            ];
            $cursor->addMonth();
        }

        return $periods;
    }

    /**
     * @param  list<array{year: int, month: int}>  $periods
     * @return list<array{year: int, month: int}>
     */
    private function normalizePeriods(array $periods): array
    {
        $unique = [];

        foreach ($periods as $period) {
            $year = (int) ($period['year'] ?? 0);
            $month = (int) ($period['month'] ?? 0);

            if ($year < 2020 || $month < 1 || $month > 12) {
                continue;
            }

            $key = sprintf('%04d-%02d', $year, $month);
            $unique[$key] = ['year' => $year, 'month' => $month];
        }

        ksort($unique);

        return array_values($unique);
    }
}
