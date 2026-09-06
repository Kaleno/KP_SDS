<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Schedule;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceSessionService
{
    public function open(Schedule $schedule, User $opener, CarbonInterface $date): AttendanceSession
    {
        if (! $schedule->is_active) {
            throw ValidationException::withMessages([
                'schedule' => 'Jadwal ini tidak aktif.',
            ]);
        }

        if ((int) $schedule->day_of_week !== (int) $date->isoWeekday()) {
            throw ValidationException::withMessages([
                'schedule' => 'Sesi hanya bisa dibuka pada hari sesuai slot jadwal.',
            ]);
        }

        return DB::transaction(function () use ($schedule, $opener, $date) {
            $session = AttendanceSession::query()->firstOrCreate(
                [
                    'schedule_id' => $schedule->id,
                    'session_date' => $date->toDateString(),
                ],
                ['opened_by_user_id' => $opener->id],
            );

            $this->syncMembers($session);

            return $session;
        });
    }

    public function syncMembers(AttendanceSession $session): void
    {
        $session->loadMissing('schedule.halaqah.activeMembers');

        foreach ($session->schedule->halaqah->activeMembers as $member) {
            Attendance::query()->firstOrCreate(
                [
                    'attendance_session_id' => $session->id,
                    'santri_id' => $member->santri_id,
                ],
                ['status' => AttendanceStatus::Hadir],
            );
        }
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
