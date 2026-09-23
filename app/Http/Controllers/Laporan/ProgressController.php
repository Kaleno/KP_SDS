<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
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

    public function bacaanShow(Request $request, SantriProfile $santri): View
    {
        return $this->detail($request, $santri, 'bacaan');
    }

    public function hafalanIndex(Request $request): View
    {
        return view('laporan.progress.hafalan.index', $this->listData($request, 'hafalan'));
    }

    public function hafalanShow(Request $request, SantriProfile $santri): View
    {
        return $this->detail($request, $santri, 'hafalan');
    }

    /**
     * @return array{rows: list<array<string, mixed>>}
     */
    private function listData(Request $request, string $kind): array
    {
        $this->access->assertCanOperate($request->user());

        $santris = $this->access
            ->activeSantriQuery($request->user())
            ->get()
            ->sortBy(fn (SantriProfile $santri) => $santri->user->name)
            ->values();

        $ids = $santris->pluck('id')->map(fn ($id): int => (int) $id)->all();
        $map = $kind === 'bacaan'
            ? $this->progress->bacaanForMany($ids)
            : $this->progress->hafalanForMany($ids);

        $rows = [];
        foreach ($santris as $santri) {
            $rows[] = [
                'santri' => $santri,
                'data' => $map[(int) $santri->id],
            ];
        }

        return [
            'rows' => $rows,
        ];
    }

    private function detail(Request $request, SantriProfile $santri, string $kind): View
    {
        $this->access->assertSantri($request->user(), $santri);

        $santri->load('user');
        $data = $kind === 'bacaan'
            ? $this->progress->bacaanForSantri((int) $santri->id)
            : $this->progress->hafalanForSantri((int) $santri->id);

        return view('laporan.progress.'.$kind.'.show', [
            'santri' => $santri,
            'data' => $data,
        ]);
    }
}
