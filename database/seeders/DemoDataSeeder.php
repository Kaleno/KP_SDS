<?php

namespace Database\Seeders;

use App\Enums\ActivityType;
use App\Enums\AttendanceStatus;
use App\Enums\Gender;
use App\Enums\RegistrationStatus;
use App\Enums\SantriStatus;
use App\Enums\SantriTrack;
use App\Enums\SchoolLevel;
use App\Enums\SetoranCategory;
use App\Enums\SetoranStatus;
use App\Enums\SetoranSubtype;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\HafalanSetoran;
use App\Models\SantriProfile;
use App\Models\SantriRegistration;
use App\Models\User;
use App\Support\AppSettings;
use App\Support\Role;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $ketua = User::query()->updateOrCreate(
            ['username' => 'ketua'],
            [
                'name' => 'Ketua DKM',
                'email' => 'ketua@kp-sds.test',
                'password' => 'password',
                'is_active' => true,
            ],
        );
        $ketua->syncRoles([Role::Ketua]);

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

        $santriData = [
            ['nis' => '2026001', 'name' => 'Ahmad Fauzi', 'gender' => Gender::LakiLaki, 'track' => SantriTrack::Alquran],
            ['nis' => '2026002', 'name' => 'Hasan Basri', 'gender' => Gender::LakiLaki, 'track' => SantriTrack::Alquran],
            ['nis' => '2026003', 'name' => 'Yusuf Maulana', 'gender' => Gender::LakiLaki, 'track' => SantriTrack::Alquran],
            ['nis' => '2026004', 'name' => 'Siti Aisyah', 'gender' => Gender::Perempuan, 'track' => SantriTrack::Iqro],
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
                    'school_level' => SchoolLevel::Sd,
                    'track' => $row['track'],
                    'iqro_level' => $row['track'] === SantriTrack::Iqro ? 1 : null,
                    'status' => SantriStatus::Aktif,
                ],
            );
        });

        $today = now();
        $todayDate = $today->toDateString();
        $first = $profiles->first();
        $second = $profiles->skip(1)->first();

        HafalanSetoran::query()->updateOrCreate(
            [
                'santri_id' => $first->id,
                'quran_surah_id' => 1,
                'ayah_start' => 1,
                'ayah_end' => 7,
                'status' => SetoranStatus::Lulus,
                'setoran_date' => $todayDate,
            ],
            [
                'ustaz_user_id' => $ustaz->id,
                'activity_type' => ActivityType::Ngaji,
                'category' => SetoranCategory::Bacaan,
                'subtype' => SetoranSubtype::Alquran,
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
                'setoran_date' => $todayDate,
            ],
            [
                'ustaz_user_id' => $ustaz->id,
                'activity_type' => ActivityType::Ngaji,
                'category' => SetoranCategory::Bacaan,
                'subtype' => SetoranSubtype::Alquran,
                'note' => 'Demo Al-Fatihah ulang',
            ],
        );

        if ($today->isWeekday()) {
            $session = AttendanceSession::query()->firstOrCreate(
                ['session_date' => $todayDate],
                ['opened_by_user_id' => $ustaz->id],
            );

            $demoStatuses = [AttendanceStatus::Hadir, AttendanceStatus::Hadir, AttendanceStatus::Alfa, AttendanceStatus::Hadir];
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
