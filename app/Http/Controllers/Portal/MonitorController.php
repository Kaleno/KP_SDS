<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Services\SantriMonitor;
use App\Support\PortalAccess;
use App\Support\Role;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonitorController extends Controller
{
    public function __construct(
        private SantriMonitor $monitor,
        private PortalAccess $access,
    ) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $year = AcademicYear::query()->aktif()->first();
        $children = collect();
        $santri = null;

        if ($user->hasRole(Role::Santri)) {
            $santri = $user->santriProfile;
            abort_unless($santri, 404);

            if ($request->filled('anak')) {
                abort_unless((int) $request->integer('anak') === (int) $santri->id, 403);
            }
        } elseif ($user->hasRole(Role::OrangTua)) {
            $children = $user->children()->with('user')->orderBy('nis')->get();

            if ($request->filled('anak')) {
                $candidate = $children->firstWhere('id', $request->integer('anak'));
                abort_unless($candidate, 403);
                $this->access->assertMonitor($user, $candidate);
                $request->session()->put('portal.anak_id', $candidate->id);
                $santri = $candidate;
            } else {
                $remembered = (int) $request->session()->get('portal.anak_id');
                $santri = $children->firstWhere('id', $remembered) ?? $children->first();
            }
        } else {
            abort(403);
        }

        $payload = $santri
            ? $this->monitor->for($santri, $year)
            : [
                'santri' => null,
                'year' => $year,
                'progress' => null,
                'setoran' => collect(),
                'attendances' => collect(),
                'membership' => null,
                'schedules' => collect(),
            ];

        return view('portal.home', [
            ...$payload,
            'children' => $children,
            'isParent' => $user->hasRole(Role::OrangTua),
        ]);
    }
}
