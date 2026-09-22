<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Ketua\AcademicYearController;
use App\Http\Controllers\Ketua\FinanceController;
use App\Http\Controllers\Ketua\HalaqahController;
use App\Http\Controllers\Ketua\HalaqahMemberController;
use App\Http\Controllers\Ketua\HolidayController;
use App\Http\Controllers\Ketua\SantriController;
use App\Http\Controllers\Ketua\SantriRegistrationController as KetuaSantriRegistrationController;
use App\Http\Controllers\Ketua\ScheduleController;
use App\Http\Controllers\Ketua\UstazController;
use App\Http\Controllers\Laporan\AttendanceRecapController;
use App\Http\Controllers\Laporan\ProgressController;
use App\Http\Controllers\Ops\AttendanceController;
use App\Http\Controllers\Ops\SantriTrackController;
use App\Http\Controllers\Ops\SetoranController;
use App\Http\Controllers\Ops\SppController;
use App\Http\Controllers\Portal\MonitorController;
use App\Http\Controllers\Portal\ProfileController as PortalProfileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\SantriRegistrationController as PublicSantriRegistrationController;
use App\Http\Controllers\SuperAdmin\KetuaAccountController;
use App\Support\Role;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/daftar', [PublicSantriRegistrationController::class, 'create'])->name('daftar.create');
    Route::post('/daftar', [PublicSantriRegistrationController::class, 'store'])->name('daftar.store');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:'.Role::SuperAdmin)->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/ketua', [KetuaAccountController::class, 'index'])->name('ketua.index');
        Route::post('/ketua', [KetuaAccountController::class, 'store'])->name('ketua.store');
        Route::patch('/ketua/{ketua}/toggle', [KetuaAccountController::class, 'toggle'])->name('ketua.toggle');
    });

    Route::middleware('role:'.Role::Ketua)->prefix('ketua')->name('ketua.')->group(function () {
        Route::get('siapkan', fn () => redirect()->route('ketua.halaqah.create'))->name('setup.create');

        Route::get('tahun-ajaran', [AcademicYearController::class, 'index'])->name('academic-years.index');
        Route::post('tahun-ajaran', [AcademicYearController::class, 'store'])->name('academic-years.store');
        Route::put('tahun-ajaran/{academicYear}', [AcademicYearController::class, 'update'])->name('academic-years.update');
        Route::patch('tahun-ajaran/{academicYear}/activate', [AcademicYearController::class, 'activate'])->name('academic-years.activate');
        Route::delete('tahun-ajaran/{academicYear}', [AcademicYearController::class, 'destroy'])->name('academic-years.destroy');

        Route::get('ustaz', [UstazController::class, 'index'])->name('ustaz.index');
        Route::get('ustaz/create', [UstazController::class, 'create'])->name('ustaz.create');
        Route::post('ustaz', [UstazController::class, 'store'])->name('ustaz.store');
        Route::get('ustaz/{ustaz}/edit', [UstazController::class, 'edit'])->name('ustaz.edit');
        Route::put('ustaz/{ustaz}', [UstazController::class, 'update'])->name('ustaz.update');
        Route::patch('ustaz/{ustaz}/toggle', [UstazController::class, 'toggle'])->name('ustaz.toggle');

        Route::get('santri', [SantriController::class, 'index'])->name('santri.index');
        Route::get('santri/create', [SantriController::class, 'create'])->name('santri.create');
        Route::post('santri', [SantriController::class, 'store'])->name('santri.store');
        Route::get('santri/{santri}/edit', [SantriController::class, 'edit'])->name('santri.edit');
        Route::put('santri/{santri}', [SantriController::class, 'update'])->name('santri.update');

        Route::get('pendaftaran', [KetuaSantriRegistrationController::class, 'index'])->name('registrations.index');
        Route::get('pendaftaran/{registration}', [KetuaSantriRegistrationController::class, 'show'])->name('registrations.show');
        Route::post('pendaftaran/{registration}/approve', [KetuaSantriRegistrationController::class, 'approve'])->name('registrations.approve');
        Route::post('pendaftaran/{registration}/reject', [KetuaSantriRegistrationController::class, 'reject'])->name('registrations.reject');

        Route::get('libur', [HolidayController::class, 'index'])->name('holidays.index');
        Route::post('libur', [HolidayController::class, 'store'])->name('holidays.store');
        Route::delete('libur/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy');

        Route::get('keuangan', [FinanceController::class, 'index'])->name('finance.index');
        Route::post('keuangan', [FinanceController::class, 'store'])->name('finance.store');
        Route::put('keuangan/spp', [FinanceController::class, 'updateSppAmount'])->name('finance.spp-amount');

        Route::get('kelas', [HalaqahController::class, 'index'])->name('halaqah.index');
        Route::get('kelas/create', [HalaqahController::class, 'create'])->name('halaqah.create');
        Route::post('kelas', [HalaqahController::class, 'store'])->name('halaqah.store');
        Route::get('kelas/{halaqah}', [HalaqahController::class, 'show'])->name('halaqah.show');
        Route::get('kelas/{halaqah}/edit', [HalaqahController::class, 'edit'])->name('halaqah.edit');
        Route::put('kelas/{halaqah}', [HalaqahController::class, 'update'])->name('halaqah.update');
        Route::patch('kelas/{halaqah}/toggle', [HalaqahController::class, 'toggle'])->name('halaqah.toggle');

        Route::post('kelas/{halaqah}/anggota', [HalaqahMemberController::class, 'store'])->name('halaqah.members.store');
        Route::post('kelas/{halaqah}/anggota/{member}/mutasi', [HalaqahMemberController::class, 'mutate'])->name('halaqah.members.mutate');
        Route::delete('kelas/{halaqah}/anggota/{member}', [HalaqahMemberController::class, 'destroy'])->name('halaqah.members.destroy');

        Route::post('kelas/{halaqah}/jadwal', [ScheduleController::class, 'store'])->name('halaqah.schedules.store');
        Route::put('kelas/{halaqah}/jadwal/{schedule}', [ScheduleController::class, 'update'])->name('halaqah.schedules.update');
        Route::delete('kelas/{halaqah}/jadwal/{schedule}', [ScheduleController::class, 'destroy'])->name('halaqah.schedules.destroy');
    });

    Route::middleware('role:'.Role::Ketua.'|'.Role::KetuaPengajar.'|'.Role::Pengajar)->prefix('ops')->name('ops.')->group(function () {
        Route::get('absensi', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('absensi/{schedule}/buka', [AttendanceController::class, 'open'])->name('attendance.open');
        Route::get('absensi/sesi/{attendanceSession}', [AttendanceController::class, 'show'])->name('attendance.show');
        Route::put('absensi/sesi/{attendanceSession}', [AttendanceController::class, 'update'])->name('attendance.update');

        Route::get('setoran', [SetoranController::class, 'index'])->name('setoran.index');
        Route::get('setoran/create', [SetoranController::class, 'create'])->name('setoran.create');
        Route::post('setoran', [SetoranController::class, 'store'])->name('setoran.store');
        Route::get('setoran/{setoran}/edit', [SetoranController::class, 'edit'])->name('setoran.edit');
        Route::put('setoran/{setoran}', [SetoranController::class, 'update'])->name('setoran.update');

        Route::middleware('role:'.Role::Ketua.'|'.Role::KetuaPengajar)->group(function () {
            Route::patch('santri/{santri}/track', [SantriTrackController::class, 'update'])->name('santri.track');
            Route::get('spp', [SppController::class, 'index'])->name('spp.index');
            Route::post('spp', [SppController::class, 'store'])->name('spp.store');
        });
    });

    Route::middleware('role:'.Role::Ketua.'|'.Role::KetuaPengajar.'|'.Role::Pengajar)->prefix('laporan')->name('laporan.')->group(function () {
        Route::get('progress', [ProgressController::class, 'index'])->name('progress.index');
        Route::get('progress/{santri}', [ProgressController::class, 'show'])->name('progress.show');
        Route::get('absensi', AttendanceRecapController::class)->name('attendance.index');
    });

    Route::middleware('role:'.Role::Santri)->prefix('portal')->name('portal.')->group(function () {
        Route::get('/', MonitorController::class)->name('home');
        Route::get('/profil', [PortalProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profil', [PortalProfileController::class, 'update'])->name('profile.update');
    });
});

require __DIR__.'/auth.php';
