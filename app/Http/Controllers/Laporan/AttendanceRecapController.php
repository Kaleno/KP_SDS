<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SantriProfile;
use App\Support\DateQuery;
use App\Support\OperationalAccess;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceRecapController extends Controller
{
    public function __construct(private OperationalAccess $access) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $halaqahList = $this->access->halaqahQuery($user)->aktif()->orderBy('name')->get();
        $halaqahIds = $halaqahList->pluck('id');

        $from = DateQuery::ymd($request->input('date_from')) ?? now()->startOfMonth()->toDateString();
        $to = DateQuery::ymd($request->input('date_to')) ?? now()->toDateString();
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }
        $selectedHalaqah = $request->integer('halaqah_id') ?: null;

        $scopeIds = $halaqahIds;
        if ($selectedHalaqah && $halaqahIds->contains($selectedHalaqah)) {
            $scopeIds = collect([$selectedHalaqah]);
        }

        $rows = collect();
        $totals = ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alfa' => 0];

        if ($scopeIds->isNotEmpty()) {
            $aggregates = Attendance::query()
                ->select('santri_id')
                ->selectRaw("SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir")
                ->selectRaw("SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin")
                ->selectRaw("SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit")
                ->selectRaw("SUM(CASE WHEN status = 'alfa' THEN 1 ELSE 0 END) as alfa")
                ->whereHas('session', function ($query) use ($from, $to, $scopeIds): void {
                    $query->whereDate('session_date', '>=', $from)
                        ->whereDate('session_date', '<=', $to)
                        ->whereHas('schedule', fn ($schedule) => $schedule->whereIn('halaqah_id', $scopeIds));
                })
                ->groupBy('santri_id')
                ->get();

            $profiles = SantriProfile::query()
                ->with('user')
                ->whereIn('id', $aggregates->pluck('santri_id'))
                ->get()
                ->keyBy('id');

            $rows = $aggregates->map(function (Attendance $row) use ($profiles, &$totals): array {
                $hadir = (int) $row->hadir;
                $izin = (int) $row->izin;
                $sakit = (int) $row->sakit;
                $alfa = (int) $row->alfa;
                $meetings = $hadir + $izin + $sakit + $alfa;
                $totals['hadir'] += $hadir;
                $totals['izin'] += $izin;
                $totals['sakit'] += $sakit;
                $totals['alfa'] += $alfa;

                return [
                    'santri' => $profiles->get($row->santri_id),
                    'hadir' => $hadir,
                    'izin' => $izin,
                    'sakit' => $sakit,
                    'alfa' => $alfa,
                    'meetings' => $meetings,
                    'percent' => $meetings > 0 ? round($hadir / $meetings * 100, 1) : 0,
                ];
            })->sortBy(fn (array $row) => $row['santri']?->user->name)->values();
        }

        $meetingTotal = array_sum($totals);

        return view('laporan.attendance.index', [
            'halaqahList' => $halaqahList,
            'selectedHalaqah' => $selectedHalaqah,
            'from' => $from,
            'to' => $to,
            'rows' => $rows,
            'totals' => $totals,
            'halaqahPercent' => $meetingTotal > 0 ? round($totals['hadir'] / $meetingTotal * 100, 1) : 0,
        ]);
    }
}
