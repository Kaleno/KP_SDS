<?php

namespace App\Http\Controllers;

use App\Services\OperationalDashboard;
use App\Support\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private OperationalDashboard $dashboard) {}

    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasRole(Role::Santri) || $user->hasRole(Role::OrangTua)) {
            return redirect()->route('portal.home');
        }

        $role = $user->getRoleNames()->first();
        $isKetua = $user->hasRole(Role::Ketua);
        $isUstaz = $user->hasRole(Role::Ustaz);

        return view('dashboard', [
            'roleLabel' => $role ? Role::label($role) : 'Pengguna',
            'isSuperAdmin' => $user->hasRole(Role::SuperAdmin),
            'isKetua' => $isKetua,
            'isUstaz' => $isUstaz,
            'overview' => ($isKetua || $isUstaz) ? $this->dashboard->for($user) : null,
        ]);
    }
}
