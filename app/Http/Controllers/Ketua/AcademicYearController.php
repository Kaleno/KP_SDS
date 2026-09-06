<?php

namespace App\Http\Controllers\Ketua;

use App\Http\Requests\Ketua\StoreAcademicYearRequest;
use App\Http\Requests\Ketua\UpdateAcademicYearRequest;
use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AcademicYearController extends KetuaController
{
    public function index(): View
    {
        return view('ketua.academic-years.index', [
            'years' => AcademicYear::query()->orderByDesc('start_date')->get(),
        ]);
    }

    public function store(StoreAcademicYearRequest $request): RedirectResponse
    {
        $year = AcademicYear::query()->create($request->safe()->except('is_active') + [
            'is_active' => false,
        ]);

        if ($request->boolean('is_active') || AcademicYear::query()->where('is_active', true)->doesntExist()) {
            $year->markAsActive();
        }

        return back()->with('status', 'Tahun ajaran disimpan.');
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->update($request->safe()->except('is_active'));

        if ($request->boolean('is_active')) {
            $academicYear->markAsActive();
        }

        return back()->with('status', 'Tahun ajaran diperbarui.');
    }

    public function activate(AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->markAsActive();

        return back()->with('status', "{$academicYear->name} dijadikan tahun ajaran aktif.");
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        if ($academicYear->halaqah()->exists()) {
            return back()->with('status', 'Tidak bisa dihapus: masih dipakai halaqah.');
        }

        $academicYear->delete();

        return back()->with('status', 'Tahun ajaran dihapus.');
    }
}
