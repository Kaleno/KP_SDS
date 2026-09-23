<?php

namespace App\Services;

use App\Models\Halaqah;
use App\Models\Schedule;
use App\Support\WeekDay;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveKelas
{
    public function __construct(
        private PlaceDefaults $defaults,
        private HalaqahMembershipService $memberships,
        private ScheduleConflictChecker $conflicts,
    ) {}

    /**
     * @param  array{name: string, ustaz_user_id: int, days: list<int>, start_time: string, end_time: string, is_active?: bool}  $data
     */
    public function handle(?Halaqah $existing, array $data): Halaqah
    {
        return DB::transaction(function () use ($existing, $data): Halaqah {
            $year = $existing?->academicYear ?? $this->defaults->activeYear();
            $days = array_values(array_unique(array_map('intval', $data['days'])));
            $start = $this->withSeconds((string) $data['start_time']);
            $end = $this->withSeconds((string) $data['end_time']);

            $halaqah = $existing ?? new Halaqah;
            $halaqah->fill([
                'name' => $data['name'],
                'ustaz_user_id' => $data['ustaz_user_id'],
                'academic_year_id' => $year->id,
                'is_active' => $data['is_active'] ?? true,
            ]);
            $halaqah->save();

            foreach ($days as $day) {
                if ($this->conflicts->ustazOverlaps($halaqah, $day, $start, $end, ignoreOwnHalaqah: true)) {
                    throw ValidationException::withMessages([
                        'start_time' => 'Jam ini bentrok dengan jadwal pengajar yang sama pada '.$this->dayLabel($day).'.',
                    ]);
                }
            }

            $this->syncSchedules($halaqah, $days, $start, $end);
            $this->memberships->enrollAllActive($halaqah);

            return $halaqah->fresh(['ustaz', 'schedules', 'activeMembers.santri.user']) ?? $halaqah;
        });
    }

    /**
     * @param  list<int>  $days
     */
    private function syncSchedules(Halaqah $halaqah, array $days, string $start, string $end): void
    {
        $existing = $halaqah->schedules()->get()->keyBy(fn (Schedule $schedule): int => (int) $schedule->day_of_week);

        foreach (array_keys(WeekDay::labels()) as $day) {
            $schedule = $existing->get($day);
            $selected = in_array($day, $days, true);

            if ($selected) {
                $payload = [
                    'day_of_week' => $day,
                    'start_time' => $start,
                    'end_time' => $end,
                    'is_active' => true,
                ];

                if ($schedule) {
                    $schedule->update($payload);

                    continue;
                }

                $halaqah->schedules()->create($payload);

                continue;
            }

            if (! $schedule) {
                continue;
            }

            if ($schedule->sessions()->exists()) {
                $schedule->update(['is_active' => false]);

                continue;
            }

            $schedule->delete();
        }
    }

    private function withSeconds(string $time): string
    {
        return strlen($time) === 5 ? $time.':00' : $time;
    }

    private function dayLabel(int $day): string
    {
        return WeekDay::label($day);
    }
}
