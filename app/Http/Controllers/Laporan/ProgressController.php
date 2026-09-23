<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SantriProfile;
use App\Services\SetoranProgress;
use App\Support\OperationalAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgressController extends Controller
{
    public function __construct(
        private OperationalAccess $access,
        private SetoranProgress $progress,
    ) {}

    public function index(): RedirectResponse
    {
        return redirect()->route('laporan.progress.bacaan.index');
    }

    public function show(SantriProfile $santri): RedirectResponse
    {
        return redirect()->route('laporan.progress.bacaan.show', $santri);
    }

    public function bacaanIndex(Request $request): View
    {
        return view('laporan.progress.bacaan.index', $this->listData($request, 'bacaan'));
    }

    public function bacaanShow(Request $request, SantriProfile $santri): View|RedirectResponse
    {
        return $this->detail($request, $santri, 'bacaan');
    }

    public function hafalanIndex(Request $request): View
    {
        return view('laporan.progress.hafalan.index', $this->listData($request, 'hafalan'));
    }

    public function hafalanShow(Request $request, SantriProfile $santri): View|RedirectResponse
    {
        return $this->detail($request, $santri, 'hafalan');
    }

    /**
     * @return array{year: ?AcademicYear, rows: list<array<string, mixed>>}
     */
    private function listData(Request $request, string $kind): array
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

            $ids = $members->pluck('santri_id')->map(fn ($id): int => (int) $id)->all();
            $map = $kind === 'bacaan'
                ? $this->progress->bacaanForMany($ids, (int) $year->id)
                : $this->progress->hafalanForMany($ids, (int) $year->id);

            foreach ($members as $member) {
                $rows[] = [
                    'santri' => $member->santri,
                    'halaqah' => $member->halaqah,
                    'data' => $map[(int) $member->santri_id],
                ];
            }
        }

        return [
            'year' => $year,
            'rows' => $rows,
        ];
    }

    private function detail(Request $request, SantriProfile $santri, string $kind): View|RedirectResponse
    {
        $this->access->assertSantri($request->user(), $santri);
        $year = AcademicYear::query()->aktif()->first();
        if (! $year) {
            return redirect()
                ->route('laporan.progress.'.$kind.'.index')
                ->with('status', 'Belum ada tahun ajaran aktif.');
        }

        $santri->load('user');
        $data = $kind === 'bacaan'
            ? $this->progress->bacaanForSantri((int) $santri->id, (int) $year->id)
            : $this->progress->hafalanForSantri((int) $santri->id, (int) $year->id);

        return view('laporan.progress.'.$kind.'.show', [
            'santri' => $santri,
            'year' => $year,
            'data' => $data,
        ]);
    }
}
