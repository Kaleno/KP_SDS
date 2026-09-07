<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\SetoranStatus;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\HafalanSetoran;
use App\Models\HalaqahMember;
use App\Models\SantriProfile;
use App\Models\Schedule;
use App\Support\WeekDay;
use Illuminate\Support\Collection;

class SantriMonitor
{
    public function __construct(private HafalanProgress $progress) {}

    /**
     * @return array{
     *     santri: SantriProfile,
     *     year: AcademicYear|null,
     *     progress: HafalanProgressResult|null,
     *     setoran: Collection<int, HafalanSetoran>,
     *     attendances: Collection<int, Attendance>,
     *     membership: HalaqahMember|null,
     *     schedules: Collection<int, Schedule>,
     *     snapshot: array{
     *         dayLabel: string,
     *         todayLabel: string,
     *         todaySlots: Collection<int, Schedule>,
     *         todayAttendance: Attendance|null,
     *         todaySetoran: Collection<int, HafalanSetoran>,
     *         latestSetoran: HafalanSetoran|null,
     *         needsFollowUp: HafalanSetoran|null,
     *         attendanceCounts: array<string, int>,
     *         attendanceTotal: int
     *     }
     * }
     */
    public function for(SantriProfile $santri, ?AcademicYear $year): array
    {
        $santri->loadMissing('user');

        $membership = null;
        $schedules = collect();
        $progress = null;

        if ($year) {
            $membership = $santri->memberships()
                ->aktif()
                ->where('academic_year_id', $year->id)
                ->with([
                    'halaqah.ustaz',
                    'halaqah.schedules' => fn ($query) => $query
                        ->where('is_active', true)
                        ->with('location')
                        ->orderBy('day_of_week')
                        ->orderBy('start_time'),
                ])
                ->first();

            $schedules = $membership?->halaqah->schedules ?? collect();
            $progress = $this->progress->forSantri($santri, $year);
        }

        $setoran = HafalanSetoran::query()
            ->with('surah')
            ->where('santri_id', $santri->id)
            ->when($year, fn ($query) => $query->where('academic_year_id', $year->id))
            ->orderByDesc('setoran_date')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $attendances = Attendance::query()
            ->select('attendances.*')
            ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
            ->with(['session.schedule.location', 'session.schedule.halaqah'])
            ->where('attendances.santri_id', $santri->id)
            ->when($year, function ($query) use ($year): void {
                $query->whereHas('session.schedule.halaqah', fn ($halaqah) => $halaqah->where('academic_year_id', $year->id));
            })
            ->orderByDesc('attendance_sessions.session_date')
            ->orderByDesc('attendances.id')
            ->limit(8)
            ->get();

        $today = now()->toDateString();
        $day = now()->isoWeekday();
        $latestSetoran = $setoran->first();
        $needsFollowUp = $latestSetoran && in_array($latestSetoran->status, [SetoranStatus::Ulang, SetoranStatus::Perbaikan], true)
            ? $latestSetoran
            : null;
        $attendanceCounts = $this->attendanceCounts($santri, $year);

        return [
            'santri' => $santri,
            'year' => $year,
            'progress' => $progress,
            'setoran' => $setoran,
            'attendances' => $attendances,
            'membership' => $membership,
            'schedules' => $schedules,
            'snapshot' => [
                'dayLabel' => WeekDay::label($day),
                'todayLabel' => now()->format('d/m/Y'),
                'todaySlots' => $schedules->filter(fn (Schedule $slot): bool => (int) $slot->day_of_week === $day)->values(),
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
    private function attendanceCounts(SantriProfile $santri, ?AcademicYear $year): array
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
            ->when($year, function ($query) use ($year): void {
                $query->whereHas('session.schedule.halaqah', fn ($halaqah) => $halaqah->where('academic_year_id', $year->id));
            })
            ->groupBy('status')
            ->pluck('total', 'status');

        foreach ($rows as $status => $total) {
            $counts[(string) $status] = (int) $total;
        }

        return $counts;
    }
}
