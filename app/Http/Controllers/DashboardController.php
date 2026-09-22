<?php

namespace App\Http\Controllers;

use App\Services\HalaqahReadiness;
use App\Services\OperationalDashboard;
use App\Services\SppService;
use App\Support\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private OperationalDashboard $dashboard,
        private HalaqahReadiness $readiness,
        private SppService $spp,
    ) {}

    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasRole(Role::Santri)) {
            return redirect()->route('portal.home');
        }

        $role = $user->getRoleNames()->first();
        $isKetua = $user->hasRole(Role::Ketua);
        $isPengajar = $user->hasAnyRole(Role::teaching());

        return view('dashboard', [
            'roleLabel' => $role ? Role::label($role) : 'Pengguna',
            'isSuperAdmin' => $user->hasRole(Role::SuperAdmin),
            'isKetua' => $isKetua,
            'isUstaz' => $isPengajar,
            'overview' => ($isKetua || $isPengajar) ? $this->dashboard->for($user) : null,
            'readiness' => $isKetua ? $this->readiness->snapshot() : null,
            'sppSummary' => $isKetua ? $this->spp->summary() : null,
        ]);
    }
}
