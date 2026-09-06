<?php

namespace App\Http\Controllers\Ketua;

use App\Http\Requests\Ketua\StoreLocationRequest;
use App\Http\Requests\Ketua\UpdateLocationRequest;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LocationController extends KetuaController
{
    public function index(): View
    {
        return view('ketua.locations.index', [
            'locations' => Location::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreLocationRequest $request): RedirectResponse
    {
        Location::query()->create($request->validated());

        return back()->with('status', 'Lokasi disimpan.');
    }

    public function update(UpdateLocationRequest $request, Location $location): RedirectResponse
    {
        $location->update($request->validated());

        return back()->with('status', 'Lokasi diperbarui.');
    }

    public function destroy(Location $location): RedirectResponse
    {
        if ($location->schedules()->exists()) {
            return back()->with('status', 'Tidak bisa dihapus: masih dipakai jadwal.');
        }

        $location->delete();

        return back()->with('status', 'Lokasi dihapus.');
    }
}
