<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Enums\Gender;
use App\Enums\RegistrationStatus;
use App\Enums\SantriStatus;
use App\Enums\SantriTrack;
use App\Enums\SchoolLevel;
use App\Enums\SetoranStatus;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\HafalanSetoran;
use App\Models\Halaqah;
use App\Models\HalaqahMember;
use App\Models\Location;
use App\Models\SantriProfile;
use App\Models\SantriRegistration;
use App\Models\Schedule;
use App\Models\User;
use App\Support\AppSettings;
use App\Support\Role;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::query()->updateOrCreate(
            ['name' => '2026/2027'],
            [
                'start_date' => '2026-07-01',
                'end_date' => '2027-06-30',
                'is_active' => false,
            ],
        );
        $year->markAsActive();

        $location = Location::query()->updateOrCreate(
            ['name' => 'Masjid Utama'],
            ['description' => 'Aula tahfidz lantai 1'],
        );

        $ustaz = User::query()->updateOrCreate(
            ['username' => 'ustaz1'],
            [
                'name' => 'Ahmad',
                'email' => 'ustaz1@kp-sds.test',
                'password' => 'password',
                'is_active' => true,
            ],
        );
        $ustaz->syncRoles([Role::KetuaPengajar]);

        $pengajar = User::query()->updateOrCreate(
            ['username' => 'pengajar1'],
            [
                'name' => 'Budi',
                'email' => 'pengajar1@kp-sds.test',
                'password' => 'password',
                'is_active' => true,
            ],
        );
        $pengajar->syncRoles([Role::Pengajar]);

        User::query()->where('username', 'ketua')->update(['name' => 'Ketua DKM']);

        $santriData = [
            ['nis' => '2026001', 'name' => 'Ahmad Fauzi', 'gender' => Gender::LakiLaki, 'track' => 'alquran'],
            ['nis' => '2026002', 'name' => 'Hasan Basri', 'gender' => Gender::LakiLaki, 'track' => 'alquran'],
            ['nis' => '2026003', 'name' => 'Yusuf Maulana', 'gender' => Gender::LakiLaki, 'track' => 'alquran'],
            ['nis' => '2026004', 'name' => 'Siti Aisyah', 'gender' => Gender::Perempuan, 'track' => 'iqro'],
        ];

        $profiles = collect($santriData)->map(function (array $row) {
            $user = User::query()->updateOrCreate(
                ['username' => $row['nis']],
                [
                    'name' => $row['name'],
                    'email' => null,
                    'password' => 'password',
                    'is_active' => true,
                ],
            );
            $user->syncRoles([Role::Santri]);

            return SantriProfile::query()->updateOrCreate(
                ['nis' => $row['nis']],
                [
                    'user_id' => $user->id,
                    'gender' => $row['gender'],
                    'birth_date' => '2012-01-15',
                    'parent_name' => 'Orang Tua Demo',
                    'school_level' => 'sd',
                    'track' => $row['track'],
                    'status' => SantriStatus::Aktif,
                ],
            );
        });

        $halaqah = Halaqah::query()->updateOrCreate(
            [
                'name' => 'Halaqah Tahfidz A',
                'academic_year_id' => $year->id,
            ],
            [
                'ustaz_user_id' => $ustaz->id,
                'is_active' => true,
            ],
        );

        foreach ($profiles as $profile) {
            $already = HalaqahMember::query()
                ->where('santri_id', $profile->id)
                ->where('academic_year_id', $year->id)
                ->whereNull('ended_at')
                ->exists();

            if (! $already) {
                HalaqahMember::query()->create([
                    'halaqah_id' => $halaqah->id,
                    'santri_id' => $profile->id,
                    'academic_year_id' => $year->id,
                    'started_at' => '2026-07-01',
                ]);
            }
        }

        $slots = [
            ['day_of_week' => 1, 'start_time' => '07:00:00', 'end_time' => '08:30:00'],
            ['day_of_week' => 4, 'start_time' => '07:00:00', 'end_time' => '08:30:00'],
        ];

        foreach ($slots as $slot) {
            Schedule::query()->updateOrCreate(
                [
                    'halaqah_id' => $halaqah->id,
                    'day_of_week' => $slot['day_of_week'],
                    'start_time' => $slot['start_time'],
                ],
                [
                    'location_id' => $location->id,
                    'end_time' => $slot['end_time'],
                    'is_active' => true,
                ],
            );
        }

        $todayDow = now()->isoWeekday();
        if (! in_array($todayDow, [1, 4], true)) {
            Schedule::query()->updateOrCreate(
                [
                    'halaqah_id' => $halaqah->id,
                    'day_of_week' => $todayDow,
                    'start_time' => '07:00:00',
                ],
                [
                    'location_id' => $location->id,
                    'end_time' => '08:30:00',
                    'is_active' => true,
                ],
            );
        }

        $today = now()->toDateString();
        $first = $profiles->first();
        $second = $profiles->skip(1)->first();

        HafalanSetoran::query()->updateOrCreate(
            [
                'santri_id' => $first->id,
                'quran_surah_id' => 1,
                'ayah_start' => 1,
                'ayah_end' => 7,
                'status' => SetoranStatus::Lulus,
                'setoran_date' => $today,
            ],
            [
                'halaqah_id' => $halaqah->id,
                'ustaz_user_id' => $ustaz->id,
                'academic_year_id' => $year->id,
                'note' => 'Demo Al-Fatihah lancar',
            ],
        );

        HafalanSetoran::query()->updateOrCreate(
            [
                'santri_id' => $second->id,
                'quran_surah_id' => 1,
                'ayah_start' => 1,
                'ayah_end' => 7,
                'status' => SetoranStatus::Mengulang,
                'setoran_date' => $today,
            ],
            [
                'halaqah_id' => $halaqah->id,
                'ustaz_user_id' => $ustaz->id,
                'academic_year_id' => $year->id,
                'note' => 'Demo Al-Fatihah ulang',
            ],
        );

        $todaySchedule = Schedule::query()
            ->where('halaqah_id', $halaqah->id)
            ->where('day_of_week', now()->isoWeekday())
            ->first();

        if ($todaySchedule) {
            $session = AttendanceSession::query()->firstOrCreate(
                [
                    'schedule_id' => $todaySchedule->id,
                    'session_date' => $today,
                ],
                ['opened_by_user_id' => $ustaz->id],
            );

            $demoStatuses = [AttendanceStatus::Hadir, AttendanceStatus::Hadir, AttendanceStatus::Alfa];
            foreach ($profiles->values() as $index => $profile) {
                Attendance::query()->updateOrCreate(
                    [
                        'attendance_session_id' => $session->id,
                        'santri_id' => $profile->id,
                    ],
                    ['status' => $demoStatuses[$index] ?? AttendanceStatus::Hadir],
                );
            }
        }

        AppSettings::setSppMonthlyAmount(AppSettings::DefaultSppMonthlyAmount);

        SantriRegistration::query()->updateOrCreate(
            [
                'name' => 'Fatimah Zahra',
                'parent_name' => 'Pak Hasan',
                'birth_date' => '2014-05-20',
            ],
            [
                'school_level' => SchoolLevel::Sd,
                'gender' => Gender::Perempuan,
                'track' => SantriTrack::Iqro,
                'status' => RegistrationStatus::Pending,
                'rejection_note' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'santri_id' => null,
            ],
        );
    }
}
