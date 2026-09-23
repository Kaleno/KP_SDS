<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\SantriProfile;
use App\Models\Schedule;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceSessionService
{
    public function __construct(private OperationalCalendar $calendar) {}

    /**
     * Ensure today's (or given date) sessions exist for matching active schedules.
     * Returns empty collection on weekends/holidays without creating rows.
     *
     * @param  iterable<int, int|string>  $halaqahIds
     * @return Collection<int, Schedule>
     */
    public function ensureForDate(User $user, iterable $halaqahIds, ?CarbonInterface $date = null): Collection
    {
        $date ??= now();
        $ids = collect($halaqahIds)
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty() || $this->calendar->isOffDay($date)) {
            return collect();
        }

        $day = (int) $date->isoWeekday();

        $slots = Schedule::query()
            ->with(['halaqah.ustaz'])
            ->whereIn('halaqah_id', $ids)
            ->where('is_active', true)
            ->where('day_of_week', $day)
            ->orderBy('start_time')
            ->get();

        foreach ($slots as $slot) {
            $this->open($slot, $user, $date);
        }

        $slots->load([
            'sessions' => fn ($query) => $query->whereDate('session_date', $date->toDateString()),
        ]);

        return $slots;
    }

    public function open(Schedule $schedule, User $opener, CarbonInterface $date): AttendanceSession
    {
        if (! $schedule->is_active) {
            throw ValidationException::withMessages([
                'schedule' => 'Jadwal ini tidak aktif.',
            ]);
        }

        if ($this->calendar->isOffDay($date)) {
            throw ValidationException::withMessages([
                'schedule' => $this->calendar->offDayMessage($date),
            ]);
        }

        if ((int) $schedule->day_of_week !== (int) $date->isoWeekday()) {
            throw ValidationException::withMessages([
                'schedule' => 'Sesi hanya bisa dibuka pada hari sesuai slot jadwal.',
            ]);
        }

        return DB::transaction(function () use ($schedule, $opener, $date) {
            $sessionDate = $date->toDateString();

            $session = AttendanceSession::query()
                ->where('schedule_id', $schedule->id)
                ->whereDate('session_date', $sessionDate)
                ->first();

            if ($session === null) {
                $session = AttendanceSession::query()->create([
                    'schedule_id' => $schedule->id,
                    'session_date' => $sessionDate,
                    'opened_by_user_id' => $opener->id,
                ]);
            }

            $this->syncMembers($session);

            return $session;
        });
    }

    public function syncMembers(AttendanceSession $session): void
    {
        SantriProfile::query()->aktif()->each(function (SantriProfile $santri) use ($session): void {
            Attendance::query()->firstOrCreate(
                [
                    'attendance_session_id' => $session->id,
                    'santri_id' => $santri->id,
                ],
                ['status' => AttendanceStatus::Hadir],
            );
        });
    }

    /**
     * @param  array<int|string, array{status: string, note?: string|null}>  $rows
     */
    public function save(AttendanceSession $session, array $rows): void
    {
        $session->load('attendances');

        foreach ($session->attendances as $attendance) {
            $row = $rows[$attendance->santri_id] ?? $rows[(string) $attendance->santri_id] ?? null;
            if ($row === null || empty($row['status'])) {
                throw ValidationException::withMessages([
                    'rows' => 'Semua santri wajib punya status kehadiran.',
                ]);
            }

            $attendance->update([
                'status' => $row['status'],
                'note' => $row['note'] ?? null,
            ]);
        }
    }
}
