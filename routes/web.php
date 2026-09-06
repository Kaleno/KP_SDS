<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Ketua\AcademicYearController;
use App\Http\Controllers\Laporan\AttendanceRecapController;
use App\Http\Controllers\Laporan\ProgressController;
use App\Http\Controllers\Ops\AttendanceController;
use App\Http\Controllers\Ops\SetoranController;
use App\Http\Controllers\Portal\MonitorController;
use App\Http\Controllers\Ketua\HalaqahController;
use App\Http\Controllers\Ketua\HalaqahMemberController;
use App\Http\Controllers\Ketua\LocationController;
use App\Http\Controllers\Ketua\OrangTuaController;
use App\Http\Controllers\Ketua\SantriController;
use App\Http\Controllers\Ketua\ScheduleController;
use App\Http\Controllers\Ketua\UstazController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdmin\KetuaAccountController;
use App\Support\Role;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
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
        Route::get('tahun-ajaran', [AcademicYearController::class, 'index'])->name('academic-years.index');
        Route::post('tahun-ajaran', [AcademicYearController::class, 'store'])->name('academic-years.store');
        Route::put('tahun-ajaran/{academicYear}', [AcademicYearController::class, 'update'])->name('academic-years.update');
        Route::patch('tahun-ajaran/{academicYear}/activate', [AcademicYearController::class, 'activate'])->name('academic-years.activate');
        Route::delete('tahun-ajaran/{academicYear}', [AcademicYearController::class, 'destroy'])->name('academic-years.destroy');

        Route::get('lokasi', [LocationController::class, 'index'])->name('locations.index');
        Route::post('lokasi', [LocationController::class, 'store'])->name('locations.store');
        Route::put('lokasi/{location}', [LocationController::class, 'update'])->name('locations.update');
        Route::delete('lokasi/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');

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

        Route::get('orang-tua', [OrangTuaController::class, 'index'])->name('orang-tua.index');
        Route::get('orang-tua/create', [OrangTuaController::class, 'create'])->name('orang-tua.create');
        Route::post('orang-tua', [OrangTuaController::class, 'store'])->name('orang-tua.store');
        Route::get('orang-tua/{orangTua}/edit', [OrangTuaController::class, 'edit'])->name('orang-tua.edit');
        Route::put('orang-tua/{orangTua}', [OrangTuaController::class, 'update'])->name('orang-tua.update');
        Route::post('orang-tua/{orangTua}/anak', [OrangTuaController::class, 'attachChild'])->name('orang-tua.attach-child');
        Route::delete('orang-tua/{orangTua}/anak/{santri}', [OrangTuaController::class, 'detachChild'])->name('orang-tua.detach-child');
        Route::patch('orang-tua/{orangTua}/toggle', [OrangTuaController::class, 'toggle'])->name('orang-tua.toggle');

        Route::get('halaqah', [HalaqahController::class, 'index'])->name('halaqah.index');
        Route::get('halaqah/create', [HalaqahController::class, 'create'])->name('halaqah.create');
        Route::post('halaqah', [HalaqahController::class, 'store'])->name('halaqah.store');
        Route::get('halaqah/{halaqah}', [HalaqahController::class, 'show'])->name('halaqah.show');
        Route::get('halaqah/{halaqah}/edit', [HalaqahController::class, 'edit'])->name('halaqah.edit');
        Route::put('halaqah/{halaqah}', [HalaqahController::class, 'update'])->name('halaqah.update');
        Route::patch('halaqah/{halaqah}/toggle', [HalaqahController::class, 'toggle'])->name('halaqah.toggle');

        Route::post('halaqah/{halaqah}/anggota', [HalaqahMemberController::class, 'store'])->name('halaqah.members.store');
        Route::post('halaqah/{halaqah}/anggota/{member}/mutasi', [HalaqahMemberController::class, 'mutate'])->name('halaqah.members.mutate');
        Route::delete('halaqah/{halaqah}/anggota/{member}', [HalaqahMemberController::class, 'destroy'])->name('halaqah.members.destroy');

        Route::post('halaqah/{halaqah}/jadwal', [ScheduleController::class, 'store'])->name('halaqah.schedules.store');
        Route::put('halaqah/{halaqah}/jadwal/{schedule}', [ScheduleController::class, 'update'])->name('halaqah.schedules.update');
        Route::delete('halaqah/{halaqah}/jadwal/{schedule}', [ScheduleController::class, 'destroy'])->name('halaqah.schedules.destroy');
    });

    Route::middleware('role:'.Role::Ketua.'|'.Role::Ustaz)->prefix('ops')->name('ops.')->group(function () {
        Route::get('absensi', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('absensi/{schedule}/buka', [AttendanceController::class, 'open'])->name('attendance.open');
        Route::get('absensi/sesi/{attendanceSession}', [AttendanceController::class, 'show'])->name('attendance.show');
        Route::put('absensi/sesi/{attendanceSession}', [AttendanceController::class, 'update'])->name('attendance.update');

        Route::get('setoran', [SetoranController::class, 'index'])->name('setoran.index');
        Route::get('setoran/create', [SetoranController::class, 'create'])->name('setoran.create');
        Route::post('setoran', [SetoranController::class, 'store'])->name('setoran.store');
        Route::get('setoran/{setoran}/edit', [SetoranController::class, 'edit'])->name('setoran.edit');
        Route::put('setoran/{setoran}', [SetoranController::class, 'update'])->name('setoran.update');
    });

    Route::middleware('role:'.Role::Ketua.'|'.Role::Ustaz)->prefix('laporan')->name('laporan.')->group(function () {
        Route::get('progress', [ProgressController::class, 'index'])->name('progress.index');
        Route::get('progress/{santri}', [ProgressController::class, 'show'])->name('progress.show');
        Route::get('absensi', AttendanceRecapController::class)->name('attendance.index');
    });

    Route::middleware('role:'.Role::Santri.'|'.Role::OrangTua)->prefix('portal')->name('portal.')->group(function () {
        Route::get('/', MonitorController::class)->name('home');
    });
});

require __DIR__.'/auth.php';
