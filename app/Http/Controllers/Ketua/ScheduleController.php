<?php

namespace App\Http\Controllers\Ketua;

use App\Http\Requests\Ketua\StoreScheduleRequest;
use App\Http\Requests\Ketua\UpdateScheduleRequest;
use App\Models\Halaqah;
use App\Models\Schedule;
use Illuminate\Http\RedirectResponse;

class ScheduleController extends KetuaController
{
    public function store(StoreScheduleRequest $request, Halaqah $halaqah): RedirectResponse
    {
        $halaqah->schedules()->create($request->validated());

        return back()->with('status', 'Jadwal ditambahkan.');
    }

    public function update(UpdateScheduleRequest $request, Halaqah $halaqah, Schedule $schedule): RedirectResponse
    {
        abort_unless($schedule->halaqah_id === $halaqah->id, 404);

        $schedule->update($request->validated());

        return back()->with('status', 'Jadwal diperbarui.');
    }

    public function destroy(Halaqah $halaqah, Schedule $schedule): RedirectResponse
    {
        abort_unless($schedule->halaqah_id === $halaqah->id, 404);

        $schedule->delete();

        return back()->with('status', 'Jadwal dihapus.');
    }
}
