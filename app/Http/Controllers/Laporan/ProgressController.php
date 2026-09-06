<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SantriProfile;
use App\Services\HafalanProgress;
use App\Support\OperationalAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgressController extends Controller
{
    public function __construct(
        private OperationalAccess $access,
        private HafalanProgress $progress,
    ) {}

    public function index(Request $request): View
    {
        $year = AcademicYear::query()->aktif()->first();
        $rows = [];

        if ($year) {
            $members = $this->access
                ->guidedMemberQuery($request->user())
                ->where('academic_year_id', $year->id)
                ->with(['santri.user', 'halaqah'])
                ->get()
                ->sortBy(fn ($member) => $member->santri->user->name);

            $map = $this->progress->forMany($members->pluck('santri_id')->all(), $year);

            foreach ($members as $member) {
                $rows[] = [
                    'santri' => $member->santri,
                    'halaqah' => $member->halaqah,
                    'progress' => $map[$member->santri_id],
                ];
            }
        }

        return view('laporan.progress.index', [
            'year' => $year,
            'rows' => $rows,
        ]);
    }

    public function show(Request $request, SantriProfile $santri): View|RedirectResponse
    {
        $this->access->assertSantri($request->user(), $santri);
        $year = AcademicYear::query()->aktif()->first();
        if (! $year) {
            return redirect()
                ->route('laporan.progress.index')
                ->with('status', 'Belum ada tahun ajaran aktif.');
        }
        $santri->load('user');

        return view('laporan.progress.show', [
            'santri' => $santri,
            'year' => $year,
            'progress' => $this->progress->forSantri($santri, $year),
        ]);
    }
}
