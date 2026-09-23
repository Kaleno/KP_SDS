<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\SantriMonitor;
use App\Support\Role;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonitorController extends Controller
{
    public function __construct(
        private SantriMonitor $monitor,
    ) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->hasRole(Role::Santri), 403);

        $santri = $user->santriProfile;
        abort_unless($santri, 404);

        return view('portal.home', $this->monitor->for($santri));
    }
}
