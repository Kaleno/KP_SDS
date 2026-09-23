<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\SetoranStatus;
use App\Enums\SetoranSubtype;
use App\Models\Attendance;
use App\Models\HafalanSetoran;
use App\Models\SantriProfile;
use App\Support\DateLabel;
use App\Support\WeekDay;
use Illuminate\Support\Collection;

class SantriMonitor
{
    public function __construct(
        private HafalanProgress $progress,
        private SetoranProgress $setoranProgress,
        private SppService $spp,
    ) {}

    /**
     * @return array{
     *     santri: SantriProfile,
     *     year: null,
     *     progress: HafalanProgressResult,
     *     bacaan: array<string, mixed>,
     *     hafalan: array<string, mixed>,
     *     setoran: Collection<int, HafalanSetoran>,
     *     attendances: Collection<int, Attendance>,
     *     membership: null,
     *     schedules: Collection<int, never>,
     *     payments: list<mixed>,
     *     currentPayment: mixed,
     *     sppAmount: mixed,
     *     unpaidMonths: int,
     *     snapshot: array{
     *         dayLabel: string,
     *         todayLabel: string,
     *         todaySlots: Collection<int, never>,
     *         todayAttendance: Attendance|null,
     *         todaySetoran: Collection<int, HafalanSetoran>,
     *         latestSetoran: HafalanSetoran|null,
     *         needsFollowUp: HafalanSetoran|null,
     *         attendanceCounts: array<string, int>,
     *         attendanceTotal: int
     *     }
     * }
     */
    public function for(SantriProfile $santri): array
    {
        $santri->loadMissing('user');

        $progress = $this->progress->forSantri($santri, SetoranSubtype::Alquran);
        $bacaan = $this->setoranProgress->bacaanForSantri((int) $santri->id);
        $hafalan = $this->setoranProgress->hafalanForSantri((int) $santri->id);

        $setoran = HafalanSetoran::query()
            ->with('surah')
            ->where('santri_id', $santri->id)
            ->orderByDesc('setoran_date')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $attendances = Attendance::query()
            ->select('attendances.*')
            ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
            ->with(['session'])
            ->where('attendances.santri_id', $santri->id)
            ->orderByDesc('attendance_sessions.session_date')
            ->orderByDesc('attendances.id')
            ->limit(8)
            ->get();

        $today = now()->toDateString();
        $day = now()->isoWeekday();
        $latestSetoran = $setoran->first();
        $needsFollowUp = $latestSetoran && $latestSetoran->status === SetoranStatus::Mengulang
            ? $latestSetoran
            : null;
        $attendanceCounts = $this->attendanceCounts($santri);
        $payments = $this->spp->historyFor($santri, 6);
        $currentPayment = $payments[0] ?? null;
        $unpaidMonths = $this->spp->unpaidMonthCount($santri);

        return [
            'santri' => $santri,
            'year' => null,
            'progress' => $progress,
            'bacaan' => $bacaan,
            'hafalan' => $hafalan,
            'setoran' => $setoran,
            'attendances' => $attendances,
            'membership' => null,
            'schedules' => collect(),
            'payments' => $payments,
            'currentPayment' => $currentPayment,
            'sppAmount' => $this->spp->monthlyAmount(),
            'unpaidMonths' => $unpaidMonths,
            'snapshot' => [
                'dayLabel' => WeekDay::label($day),
                'todayLabel' => DateLabel::dayMonthYear(now()),
                'todaySlots' => collect(),
                'todayAttendance' => $attendances->first(
                    fn (Attendance $row): bool => $row->session->session_date->toDateString() === $today
                ),
                'todaySetoran' => $setoran->filter(
                    fn (HafalanSetoran $item): bool => $item->setoran_date->toDateString() === $today
                )->values(),
                'latestSetoran' => $latestSetoran,
                'needsFollowUp' => $needsFollowUp,
                'attendanceCounts' => $attendanceCounts,
                'attendanceTotal' => array_sum($attendanceCounts),
            ],
        ];
    }

    /**
     * @return array<string, int>
     */
    private function attendanceCounts(SantriProfile $santri): array
    {
        $counts = [
            AttendanceStatus::Hadir->value => 0,
            AttendanceStatus::Izin->value => 0,
            AttendanceStatus::Sakit->value => 0,
            AttendanceStatus::Alfa->value => 0,
        ];

        $rows = Attendance::query()
            ->selectRaw('status, COUNT(*) as total')
            ->where('santri_id', $santri->id)
            ->groupBy('status')
            ->pluck('total', 'status');

        foreach ($rows as $status => $total) {
            $counts[(string) $status] = (int) $total;
        }

        return $counts;
    }
}
