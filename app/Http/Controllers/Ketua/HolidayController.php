<?php

namespace App\Http\Controllers\Ketua;

use App\Http\Requests\Ketua\StoreHolidayRequest;
use App\Models\Holiday;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HolidayController extends KetuaController
{
    public function index(): View
    {
        return view('ketua.holidays.index', [
            'holidays' => Holiday::query()->orderByDesc('date')->get(),
        ]);
    }

    public function store(StoreHolidayRequest $request): RedirectResponse
    {
        Holiday::query()->create($request->validated());

        return back()->with('status', 'Tanggal libur ditambahkan.');
    }

    public function destroy(Holiday $holiday): RedirectResponse
    {
        $holiday->delete();

        return back()->with('status', 'Tanggal libur dihapus.');
    }
}
