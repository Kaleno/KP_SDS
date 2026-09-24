<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\SetoranStatus;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\HafalanSetoran;
use App\Models\SantriProfile;
use App\Models\User;
use App\Support\DateLabel;
use App\Support\OperationalAccess;
use App\Support\Role;
use App\Support\WeekDay;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OperationalDashboard
{
    public function __construct(
        private OperationalAccess $access,
        private AttendanceSessionService $sessions,
        private OperationalCalendar $calendar,
    ) {}

    /**
     * @return array{
     *     dayLabel: string,
     *     todayLabel: string,
     *     isOffDay: bool,
     *     offDayMessage: string|null,
     *     stats: array{
     *         santriAktif: int,
     *         pengajar: int,
     *         setoranHariIni: int,
     *         alfaHariIni: int,
     *         hadirHariIni: int,
     *         izinHariIni: int,
     *         sakitHariIni: int,
     *         sesiHariIni: int,
     *         sudahSetorHariIni: int
     *     },
     *     attendanceRecorded: bool,
     *     todaySession: AttendanceSession|null,
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
     *         total: int
     *     }
     * }
     */
    public function for(User $user): array
    {
        $today = now();
        $todayDate = $today->toDateString();
        $day = $today->isoWeekday();
        $isOffDay = $this->calendar->isOffDay($today);

        $todaySession = $isOffDay ? null : $this->sessions->ensureForDate($user, $today);
        $santriIds = collect($this->access->activeSantriIds($user));
        $attendanceToday = $this->attendanceCountsBetween($todayDate, $todayDate);
        $setoranHariIni = HafalanSetoran::query()->whereDate('setoran_date', $todayDate)->count();
        $sudahSetorHariIni = $this->sudahSetorCount($santriIds, $todayDate);

        return [
            'dayLabel' => WeekDay::label($day),
            'todayLabel' => DateLabel::dayMonthYear($today),
            'isOffDay' => $isOffDay,
            'offDayMessage' => $isOffDay ? $this->calendar->offDayMessage($today) : null,
            'stats' => [
                'santriAktif' => $santriIds->count(),
                'pengajar' => User::query()->role(Role::teaching())->where('is_active', true)->count(),
                'setoranHariIni' => $setoranHariIni,
                'alfaHariIni' => $attendanceToday[AttendanceStatus::Alfa->value],
                'hadirHariIni' => $attendanceToday[AttendanceStatus::Hadir->value],
                'izinHariIni' => $attendanceToday[AttendanceStatus::Izin->value],
                'sakitHariIni' => $attendanceToday[AttendanceStatus::Sakit->value],
                'sesiHariIni' => $todaySession ? 1 : 0,
                'sudahSetorHariIni' => $sudahSetorHariIni,
            ],
            'attendanceRecorded' => $todaySession?->submitted_at !== null,
            'todaySession' => $todaySession,
            'recentSetoran' => $this->recentSetoran(),
            'alfaToday' => $this->alfaToday($todayDate),
            'pendingSetoran' => $this->pendingSetoran($santriIds, $todayDate),
            'followUpSetoran' => $this->followUpSetoran($todayDate),
            'week' => $this->weekSummary($today),
        ];
    }

    /**
     * Ringkasan minggu untuk beranda ketua, tanpa antrean setoran atau sesi hari ini.
     *
     * @return array{
     *     dayLabel: string,
     *     todayLabel: string,
     *     week: array{
     *         from: string,
     *         to: string,
     *         fromLabel: string,
     *         toLabel: string,
     *         hadir: int,
     *         izin: int,
     *         sakit: int,
     *         alfa: int,
     *         total: int
     *     }
     * }
     */
    public function weekRecap(): array
    {
        $today = now();

        return [
            'dayLabel' => WeekDay::label($today->isoWeekday()),
            'todayLabel' => DateLabel::dayMonthYear($today),
            'week' => $this->weekSummary($today),
        ];
    }

    /**
     * @return array{
     *     from: string,
     *     to: string,
     *     fromLabel: string,
     *     toLabel: string,
     *     hadir: int,
     *     izin: int,
     *     sakit: int,
     *     alfa: int,
     *     total: int
     * }
     */
    private function weekSummary(Carbon $today): array
    {
        $todayDate = $today->toDateString();
        $weekFrom = $today->copy()->startOfWeek(Carbon::MONDAY);
        $weekCounts = $this->attendanceCountsBetween($weekFrom->toDateString(), $todayDate);

        return [
            'from' => $weekFrom->toDateString(),
            'to' => $todayDate,
            'fromLabel' => $weekFrom->format('d/m'),
            'toLabel' => $today->format('d/m'),
            'hadir' => $weekCounts[AttendanceStatus::Hadir->value],
            'izin' => $weekCounts[AttendanceStatus::Izin->value],
            'sakit' => $weekCounts[AttendanceStatus::Sakit->value],
            'alfa' => $weekCounts[AttendanceStatus::Alfa->value],
            'total' => array_sum($weekCounts),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function attendanceCountsBetween(string $from, string $to): array
    {
        $counts = [
            AttendanceStatus::Hadir->value => 0,
            AttendanceStatus::Izin->value => 0,
            AttendanceStatus::Sakit->value => 0,
            AttendanceStatus::Alfa->value => 0,
        ];

        $rows = Attendance::query()
            ->selectRaw('status, COUNT(*) as total')
            ->whereHas('session', function ($query) use ($from, $to): void {
                $query->whereDate('session_date', '>=', $from)
                    ->whereDate('session_date', '<=', $to)
                    ->whereNotNull('submitted_at');
            })
            ->groupBy('status')
            ->pluck('total', 'status');

        foreach ($rows as $status => $total) {
            $counts[(string) $status] = (int) $total;
        }

        return $counts;
    }

    /**
     * @return Collection<int, HafalanSetoran>
     */
    private function recentSetoran(): Collection
    {
        return HafalanSetoran::query()
            ->with(['santri.user', 'surah'])
            ->orderByDesc('setoran_date')
            ->orderByDesc('id')
            ->limit(6)
            ->get();
    }

    /**
     * @return Collection<int, Attendance>
     */
    private function alfaToday(string $today): Collection
    {
        return Attendance::query()
            ->with(['santri.user', 'session'])
            ->where('status', AttendanceStatus::Alfa)
            ->whereHas('session', function ($query) use ($today): void {
                $query->whereDate('session_date', $today)
                    ->whereNotNull('submitted_at');
            })
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  Collection<int, int>  $santriIds
     */
    private function sudahSetorCount(Collection $santriIds, string $today): int
    {
        if ($santriIds->isEmpty()) {
            return 0;
        }

        return HafalanSetoran::query()
            ->whereIn('santri_id', $santriIds)
            ->whereDate('setoran_date', $today)
            ->distinct()
            ->count('santri_id');
    }

    /**
     * @param  Collection<int, int>  $santriIds
     * @return Collection<int, SantriProfile>
     */
    private function pendingSetoran(Collection $santriIds, string $today): Collection
    {
        if ($santriIds->isEmpty()) {
            return collect();
        }

        $doneIds = HafalanSetoran::query()
            ->whereIn('santri_id', $santriIds)
            ->whereDate('setoran_date', $today)
            ->pluck('santri_id');

        $pendingIds = $santriIds->diff($doneIds)->values();
        if ($pendingIds->isEmpty()) {
            return collect();
        }

        return SantriProfile::query()
            ->aktif()
            ->select('santri_profiles.*')
            ->join('users', 'users.id', '=', 'santri_profiles.user_id')
            ->with('user')
            ->whereIn('santri_profiles.id', $pendingIds)
            ->orderBy('users.name')
            ->get();
    }

    /**
     * @return Collection<int, HafalanSetoran>
     */
    private function followUpSetoran(string $today): Collection
    {
        return HafalanSetoran::query()
            ->with(['santri.user', 'surah'])
            ->whereDate('setoran_date', $today)
            ->where('status', SetoranStatus::Mengulang)
            ->orderByDesc('id')
            ->get();
    }
}
