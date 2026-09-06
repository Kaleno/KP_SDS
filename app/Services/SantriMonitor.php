<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\HafalanSetoran;
use App\Models\HalaqahMember;
use App\Models\SantriProfile;
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
     *     schedules: Collection<int, mixed>
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

        return compact('santri', 'year', 'progress', 'setoran', 'attendances', 'membership', 'schedules');
    }
}
