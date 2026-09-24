<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\SantriProfile;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceSessionService
{
    public function __construct(private OperationalCalendar $calendar) {}

    /**
     * One global session per weekday (Mon–Fri), excluding holidays.
     */
    public function ensureForDate(User $user, ?CarbonInterface $date = null): ?AttendanceSession
    {
        $date ??= now();

        if ($this->calendar->isOffDay($date)) {
            return null;
        }

        return DB::transaction(function () use ($user, $date) {
            $sessionDate = $date->toDateString();

            $session = AttendanceSession::query()
                ->whereDate('session_date', $sessionDate)
                ->first();

            if ($session === null) {
                $session = AttendanceSession::query()->create([
                    'session_date' => $sessionDate,
                    'opened_by_user_id' => $user->id,
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
        if ($this->calendar->isOffDay($session->session_date)) {
            throw ValidationException::withMessages([
                'session' => $this->calendar->offDayMessage($session->session_date),
            ]);
        }

        DB::transaction(function () use ($session, $rows): void {
            foreach ($rows as $santriId => $row) {
                Attendance::query()
                    ->where('attendance_session_id', $session->id)
                    ->where('santri_id', (int) $santriId)
                    ->update([
                        'status' => $row['status'],
                        'note' => $row['note'] ?? null,
                    ]);
            }

            $session->forceFill(['submitted_at' => now()])->save();
        });
    }
}
