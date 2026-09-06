<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\HafalanSetoran;
use App\Models\Halaqah;
use App\Models\SantriProfile;
use App\Support\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasRole(Role::Santri) || $user->hasRole(Role::OrangTua)) {
            return redirect()->route('portal.home');
        }

        $role = $user->getRoleNames()->first();
        $isKetua = $user->hasRole(Role::Ketua);
        $year = AcademicYear::query()->aktif()->first();

        $stats = null;
        if ($isKetua) {
            $stats = [
                'halaqah' => Halaqah::query()->aktif()
                    ->when($year, fn ($query) => $query->where('academic_year_id', $year->id))
                    ->count(),
                'santriAktif' => SantriProfile::query()->aktif()->count(),
                'setoranHariIni' => HafalanSetoran::query()
                    ->whereDate('setoran_date', today())
                    ->when($year, fn ($query) => $query->where('academic_year_id', $year->id))
                    ->count(),
                'alfaHariIni' => Attendance::query()
                    ->where('status', AttendanceStatus::Alfa)
                    ->whereHas('session', function ($query) use ($year): void {
                        $query->whereDate('session_date', today());
                        if ($year) {
                            $query->whereHas('schedule.halaqah', fn ($halaqah) => $halaqah->where('academic_year_id', $year->id));
                        }
                    })
                    ->count(),
            ];
        }

        return view('dashboard', [
            'roleLabel' => $role ? Role::label($role) : 'Pengguna',
            'isSuperAdmin' => $user->hasRole(Role::SuperAdmin),
            'isKetua' => $isKetua,
            'isUstaz' => $user->hasRole(Role::Ustaz),
            'year' => $year,
            'stats' => $stats,
        ]);
    }
}
