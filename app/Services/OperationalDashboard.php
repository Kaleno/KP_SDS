<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\SetoranStatus;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\HafalanSetoran;
use App\Models\Halaqah;
use App\Models\SantriProfile;
use App\Models\Schedule;
use App\Models\User;
use App\Support\OperationalAccess;
use App\Support\Role;
use App\Support\WeekDay;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OperationalDashboard
{
    public function __construct(private OperationalAccess $access) {}

    /**
     * @return array{
     *     year: AcademicYear|null,
     *     dayLabel: string,
     *     todayLabel: string,
     *     stats: array{
     *         halaqah: int,
     *         santriAktif: int,
     *         setoranHariIni: int,
     *         alfaHariIni: int,
     *         hadirHariIni: int,
     *         izinHariIni: int,
     *         sakitHariIni: int,
     *         slotHariIni: int,
     *         sesiTerbuka: int,
     *         ustaz: int,
     *         anggotaHalaqah: int,
     *         sudahSetorHariIni: int
     *     },
     *     todaySlots: Collection<int, Schedule>,
     *     halaqahRows: Collection<int, array{halaqah: Halaqah, members: int, setoranToday: int, alfaToday: int}>,
     *     recentSetoran: Collection<int, HafalanSetoran>,
     *     alfaToday: Collection<int, Attendance>,
     *     pendingSetoran: Collection<int, SantriProfile>,
     *     followUpSetoran: Collection<int, HafalanSetoran>,
     *     week: array{
     *         from: string,
     *         to: string,
     *         fromLabel: string,
     *         toLabel: string,
     *         hadir: int,
     *         izin: int,
     *         sakit: int,
     *         alfa: int,
     *         total: int,
     *         alfaNames: Collection<int, Attendance>,
     *         missing: Collection<int, SantriProfile>
     *     }
     * }
     */
    public function for(User $user): array
    {
        $year = AcademicYear::query()->aktif()->first();
        $today = now()->toDateString();
        $day = now()->isoWeekday();
        $halaqahIds = $this->access->halaqahQuery($user)->aktif()->pluck('id');

        $todaySlots = $halaqahIds->isEmpty()
            ? collect()
            : Schedule::query()
                ->with(['halaqah.ustaz', 'location'])
                ->with(['sessions' => fn ($query) => $query->whereDate('session_date', $today)])
                ->whereIn('halaqah_id', $halaqahIds)
                ->where('is_active', true)
                ->where('day_of_week', $day)
                ->orderBy('start_time')
                ->get();

        $attendanceToday = $this->attendanceCountsBetween($halaqahIds, $today, $today, $year);
        $setoranHariIni = $this->setoranCountToday($halaqahIds, $today, $year);
        $anggotaIds = $this->guidedAktifSantriIds($user);
        $sudahSetorHariIni = $this->sudahSetorCount($anggotaIds, $today, $year);

        $santriAktif = $user->hasRole(Role::Ketua)
            ? SantriProfile::query()->aktif()->count()
            : $anggotaIds->count();

        $halaqahList = $halaqahIds->isEmpty()
            ? collect()
            : Halaqah::query()
                ->with('ustaz')
                ->withCount('activeMembers')
                ->whereIn('id', $halaqahIds)
                ->orderBy('name')
                ->get();

        $setoranByHalaqah = $this->countByHalaqah(
            HafalanSetoran::query()
                ->whereIn('halaqah_id', $halaqahIds)
                ->whereDate('setoran_date', $today)
                ->when($year, fn ($query) => $query->where('academic_year_id', $year->id)),
        );

        $alfaByHalaqah = $this->countByHalaqahFromAttendance($halaqahIds, $today, $year, AttendanceStatus::Alfa);

        $halaqahRows = $halaqahList->map(fn (Halaqah $halaqah): array => [
            'halaqah' => $halaqah,
            'members' => (int) $halaqah->active_members_count,
            'setoranToday' => (int) ($setoranByHalaqah[$halaqah->id] ?? 0),
            'alfaToday' => (int) ($alfaByHalaqah[$halaqah->id] ?? 0),
        ]);

        $weekFrom = now()->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
        $weekTo = $today;
        $weekCounts = $this->attendanceCountsBetween($halaqahIds, $weekFrom, $weekTo, $year);

        return [
            'year' => $year,
            'dayLabel' => WeekDay::label($day),
            'todayLabel' => now()->format('d/m/Y'),
            'stats' => [
                'halaqah' => $halaqahList->count(),
                'santriAktif' => $santriAktif,
                'setoranHariIni' => $setoranHariIni,
                'alfaHariIni' => $attendanceToday[AttendanceStatus::Alfa->value],
                'hadirHariIni' => $attendanceToday[AttendanceStatus::Hadir->value],
                'izinHariIni' => $attendanceToday[AttendanceStatus::Izin->value],
                'sakitHariIni' => $attendanceToday[AttendanceStatus::Sakit->value],
                'slotHariIni' => $todaySlots->count(),
                'sesiTerbuka' => $todaySlots->filter(fn (Schedule $slot) => $slot->sessions->isNotEmpty())->count(),
                'ustaz' => $halaqahList->pluck('ustaz_user_id')->unique()->count(),
                'anggotaHalaqah' => $anggotaIds->count(),
                'sudahSetorHariIni' => $sudahSetorHariIni,
            ],
            'todaySlots' => $todaySlots,
            'halaqahRows' => $halaqahRows,
            'recentSetoran' => $this->recentSetoran($halaqahIds, $year),
            'alfaToday' => $this->alfaToday($halaqahIds, $today, $year),
            'pendingSetoran' => $this->pendingSetoran($user, $year, $today),
            'followUpSetoran' => $this->followUpSetoran($halaqahIds, $today, $year),
            'week' => [
                'from' => $weekFrom,
                'to' => $weekTo,
                'fromLabel' => now()->copy()->startOfWeek(Carbon::MONDAY)->format('d/m'),
                'toLabel' => now()->format('d/m'),
                'hadir' => $weekCounts[AttendanceStatus::Hadir->value],
                'izin' => $weekCounts[AttendanceStatus::Izin->value],
                'sakit' => $weekCounts[AttendanceStatus::Sakit->value],
                'alfa' => $weekCounts[AttendanceStatus::Alfa->value],
                'total' => array_sum($weekCounts),
                'alfaNames' => $this->alfaBetween($halaqahIds, $weekFrom, $weekTo, $year),
                'missing' => $this->missingAttendanceBetween($anggotaIds, $halaqahIds, $weekFrom, $weekTo, $year),
            ],
        ];
    }

    /**
     * @param  Collection<int, int>  $halaqahIds
     * @return array<string, int>
     */
    private function attendanceCountsBetween(Collection $halaqahIds, string $from, string $to, ?AcademicYear $year): array
    {
        $counts = [
            AttendanceStatus::Hadir->value => 0,
            AttendanceStatus::Izin->value => 0,
            AttendanceStatus::Sakit->value => 0,
            AttendanceStatus::Alfa->value => 0,
        ];

        if ($halaqahIds->isEmpty()) {
            return $counts;
        }

        $rows = Attendance::query()
            ->selectRaw('status, COUNT(*) as total')
            ->whereHas('session', function ($query) use ($halaqahIds, $from, $to, $year): void {
                $query->whereDate('session_date', '>=', $from)
                    ->whereDate('session_date', '<=', $to)
                    ->whereHas('schedule', function ($schedule) use ($halaqahIds, $year): void {
                        $schedule->whereIn('halaqah_id', $halaqahIds);
                        if ($year) {
                            $schedule->whereHas('halaqah', fn ($halaqah) => $halaqah->where('academic_year_id', $year->id));
                        }
                    });
            })
            ->groupBy('status')
            ->pluck('total', 'status');

        foreach ($rows as $status => $total) {
            $counts[(string) $status] = (int) $total;
        }

        return $counts;
    }

    /**
     * @param  Collection<int, int>  $halaqahIds
     */
    private function setoranCountToday(Collection $halaqahIds, string $today, ?AcademicYear $year): int
    {
        if ($halaqahIds->isEmpty()) {
            return 0;
        }

        return HafalanSetoran::query()
            ->whereIn('halaqah_id', $halaqahIds)
            ->whereDate('setoran_date', $today)
            ->when($year, fn ($query) => $query->where('academic_year_id', $year->id))
            ->count();
    }

    /**
     * @param  Builder<HafalanSetoran>  $query
     * @return Collection<int, int>
     */
    private function countByHalaqah($query): Collection
    {
        return $query
            ->selectRaw('halaqah_id, COUNT(*) as total')
            ->groupBy('halaqah_id')
            ->pluck('total', 'halaqah_id');
    }

    /**
     * @param  Collection<int, int>  $halaqahIds
     * @return Collection<int, int>
     */
    private function countByHalaqahFromAttendance(Collection $halaqahIds, string $today, ?AcademicYear $year, AttendanceStatus $status): Collection
    {
        if ($halaqahIds->isEmpty()) {
            return collect();
        }

        return Attendance::query()
            ->selectRaw('schedules.halaqah_id as halaqah_id, COUNT(*) as total')
            ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
            ->join('schedules', 'schedules.id', '=', 'attendance_sessions.schedule_id')
            ->where('attendances.status', $status)
            ->whereDate('attendance_sessions.session_date', $today)
            ->whereIn('schedules.halaqah_id', $halaqahIds)
            ->when($year, fn ($query) => $query->whereHas('session.schedule.halaqah', fn ($halaqah) => $halaqah->where('academic_year_id', $year->id)))
            ->groupBy('schedules.halaqah_id')
            ->pluck('total', 'halaqah_id');
    }

    /**
     * @param  Collection<int, int>  $halaqahIds
     * @return Collection<int, HafalanSetoran>
     */
    private function recentSetoran(Collection $halaqahIds, ?AcademicYear $year): Collection
    {
        if ($halaqahIds->isEmpty()) {
            return collect();
        }

        return HafalanSetoran::query()
            ->with(['santri.user', 'surah', 'halaqah'])
            ->whereIn('halaqah_id', $halaqahIds)
            ->when($year, fn ($query) => $query->where('academic_year_id', $year->id))
            ->orderByDesc('setoran_date')
            ->orderByDesc('id')
            ->limit(6)
            ->get();
    }

    /**
     * @param  Collection<int, int>  $halaqahIds
     * @return Collection<int, Attendance>
     */
    private function alfaToday(Collection $halaqahIds, string $today, ?AcademicYear $year): Collection
    {
        if ($halaqahIds->isEmpty()) {
            return collect();
        }

        return Attendance::query()
            ->with(['santri.user', 'session.schedule.halaqah'])
            ->where('status', AttendanceStatus::Alfa)
            ->whereHas('session', function ($query) use ($halaqahIds, $today, $year): void {
                $query->whereDate('session_date', $today)
                    ->whereHas('schedule', function ($schedule) use ($halaqahIds, $year): void {
                        $schedule->whereIn('halaqah_id', $halaqahIds);
                        if ($year) {
                            $schedule->whereHas('halaqah', fn ($halaqah) => $halaqah->where('academic_year_id', $year->id));
                        }
                    });
            })
            ->orderBy('id')
            ->limit(8)
            ->get();
    }

    /**
     * @return Collection<int, int>
     */
    private function guidedAktifSantriIds(User $user): Collection
    {
        return $this->access->guidedMemberQuery($user)
            ->whereHas('santri', fn ($query) => $query->aktif())
            ->pluck('santri_id')
            ->unique()
            ->values();
    }

    /**
     * @param  Collection<int, int>  $santriIds
     */
    private function sudahSetorCount(Collection $santriIds, string $today, ?AcademicYear $year): int
    {
        if ($santriIds->isEmpty()) {
            return 0;
        }

        return HafalanSetoran::query()
            ->whereIn('santri_id', $santriIds)
            ->whereDate('setoran_date', $today)
            ->when($year, fn ($query) => $query->where('academic_year_id', $year->id))
            ->distinct()
            ->count('santri_id');
    }

    /**
     * @return Collection<int, SantriProfile>
     */
    private function pendingSetoran(User $user, ?AcademicYear $year, string $today): Collection
    {
        $memberIds = $this->guidedAktifSantriIds($user);
        if ($memberIds->isEmpty()) {
            return collect();
        }

        $doneIds = HafalanSetoran::query()
            ->whereIn('santri_id', $memberIds)
            ->whereDate('setoran_date', $today)
            ->when($year, fn ($query) => $query->where('academic_year_id', $year->id))
            ->pluck('santri_id');

        $pendingIds = $memberIds->diff($doneIds)->values();
        if ($pendingIds->isEmpty()) {
            return collect();
        }

        return SantriProfile::query()
            ->aktif()
            ->select('santri_profiles.*')
            ->join('users', 'users.id', '=', 'santri_profiles.user_id')
            ->with([
                'user',
                'memberships' => fn ($query) => $query
                    ->aktif()
                    ->when($year, fn ($membership) => $membership->where('academic_year_id', $year->id))
                    ->with('halaqah'),
            ])
            ->whereIn('santri_profiles.id', $pendingIds)
            ->orderBy('users.name')
            ->limit(8)
            ->get();
    }

    /**
     * @param  Collection<int, int>  $halaqahIds
     * @return Collection<int, HafalanSetoran>
     */
    private function followUpSetoran(Collection $halaqahIds, string $today, ?AcademicYear $year): Collection
    {
        if ($halaqahIds->isEmpty()) {
            return collect();
        }

        return HafalanSetoran::query()
            ->with(['santri.user', 'surah', 'halaqah'])
            ->whereIn('halaqah_id', $halaqahIds)
            ->whereDate('setoran_date', $today)
            ->whereIn('status', [SetoranStatus::Ulang, SetoranStatus::Perbaikan])
            ->when($year, fn ($query) => $query->where('academic_year_id', $year->id))
            ->orderByDesc('id')
            ->limit(6)
            ->get();
    }

    /**
     * @param  Collection<int, int>  $halaqahIds
     * @return Collection<int, Attendance>
     */
    private function alfaBetween(Collection $halaqahIds, string $from, string $to, ?AcademicYear $year): Collection
    {
        if ($halaqahIds->isEmpty()) {
            return collect();
        }

        return Attendance::query()
            ->with(['santri.user', 'session.schedule.halaqah'])
            ->where('status', AttendanceStatus::Alfa)
            ->whereHas('session', function ($query) use ($halaqahIds, $from, $to, $year): void {
                $query->whereDate('session_date', '>=', $from)
                    ->whereDate('session_date', '<=', $to)
                    ->whereHas('schedule', function ($schedule) use ($halaqahIds, $year): void {
                        $schedule->whereIn('halaqah_id', $halaqahIds);
                        if ($year) {
                            $schedule->whereHas('halaqah', fn ($halaqah) => $halaqah->where('academic_year_id', $year->id));
                        }
                    });
            })
            ->orderByDesc('id')
            ->limit(8)
            ->get();
    }

    /**
     * @param  Collection<int, int>  $santriIds
     * @param  Collection<int, int>  $halaqahIds
     * @return Collection<int, SantriProfile>
     */
    private function missingAttendanceBetween(Collection $santriIds, Collection $halaqahIds, string $from, string $to, ?AcademicYear $year): Collection
    {
        if ($santriIds->isEmpty() || $halaqahIds->isEmpty()) {
            return collect();
        }

        $hasSession = AttendanceSession::query()
            ->whereDate('session_date', '>=', $from)
            ->whereDate('session_date', '<=', $to)
            ->whereHas('schedule', function ($schedule) use ($halaqahIds, $year): void {
                $schedule->whereIn('halaqah_id', $halaqahIds);
                if ($year) {
                    $schedule->whereHas('halaqah', fn ($halaqah) => $halaqah->where('academic_year_id', $year->id));
                }
            })
            ->exists();

        if (! $hasSession) {
            return collect();
        }

        $recordedIds = Attendance::query()
            ->whereIn('santri_id', $santriIds)
            ->whereHas('session', function ($query) use ($halaqahIds, $from, $to, $year): void {
                $query->whereDate('session_date', '>=', $from)
                    ->whereDate('session_date', '<=', $to)
                    ->whereHas('schedule', function ($schedule) use ($halaqahIds, $year): void {
                        $schedule->whereIn('halaqah_id', $halaqahIds);
                        if ($year) {
                            $schedule->whereHas('halaqah', fn ($halaqah) => $halaqah->where('academic_year_id', $year->id));
                        }
                    });
            })
            ->pluck('santri_id')
            ->unique();

        $missingIds = $santriIds->diff($recordedIds)->values();
        if ($missingIds->isEmpty()) {
            return collect();
        }

        return SantriProfile::query()
            ->select('santri_profiles.*')
            ->join('users', 'users.id', '=', 'santri_profiles.user_id')
            ->with('user')
            ->whereIn('santri_profiles.id', $missingIds)
            ->orderBy('users.name')
            ->limit(8)
            ->get();
    }
}
