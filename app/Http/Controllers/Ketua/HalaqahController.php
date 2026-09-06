<?php

namespace App\Http\Controllers\Ketua;

use App\Enums\SantriStatus;
use App\Http\Requests\Ketua\StoreHalaqahRequest;
use App\Http\Requests\Ketua\UpdateHalaqahRequest;
use App\Models\AcademicYear;
use App\Models\Halaqah;
use App\Models\HalaqahMember;
use App\Models\Location;
use App\Models\SantriProfile;
use App\Models\User;
use App\Support\Role;
use App\Support\WeekDay;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HalaqahController extends KetuaController
{
    public function index(): View
    {
        return view('ketua.halaqah.index', [
            'halaqahList' => Halaqah::query()
                ->with(['academicYear', 'ustaz'])
                ->withCount('activeMembers')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('ketua.halaqah.create', $this->formOptions());
    }

    public function store(StoreHalaqahRequest $request): RedirectResponse
    {
        $halaqah = Halaqah::query()->create($request->validated());

        return redirect()->route('ketua.halaqah.show', $halaqah)->with('status', 'Halaqah dibuat. Tambahkan anggota dan jadwal.');
    }

    public function show(Halaqah $halaqah): View
    {
        $halaqah->load(['academicYear', 'ustaz', 'activeMembers.santri.user', 'schedules.location']);

        $occupiedSantriIds = HalaqahMember::query()
            ->where('academic_year_id', $halaqah->academic_year_id)
            ->whereNull('ended_at')
            ->pluck('santri_id');

        $availableSantri = SantriProfile::query()
            ->aktif()
            ->with('user')
            ->whereNotIn('id', $occupiedSantriIds)
            ->orderBy('nis')
            ->get();

        $targetHalaqah = Halaqah::query()
            ->where('academic_year_id', $halaqah->academic_year_id)
            ->where('id', '!=', $halaqah->id)
            ->orderBy('name')
            ->get();

        return view('ketua.halaqah.show', [
            'halaqah' => $halaqah,
            'availableSantri' => $availableSantri,
            'targetHalaqah' => $targetHalaqah,
            'locations' => Location::query()->orderBy('name')->get(),
            'days' => WeekDay::labels(),
        ]);
    }

    public function edit(Halaqah $halaqah): View
    {
        return view('ketua.halaqah.edit', [
            'halaqah' => $halaqah,
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdateHalaqahRequest $request, Halaqah $halaqah): RedirectResponse
    {
        $halaqah->update($request->validated());

        return redirect()->route('ketua.halaqah.show', $halaqah)->with('status', 'Halaqah diperbarui.');
    }

    public function toggle(Halaqah $halaqah): RedirectResponse
    {
        $halaqah->update(['is_active' => ! $halaqah->is_active]);

        return back()->with('status', $halaqah->is_active ? 'Halaqah diaktifkan.' : 'Halaqah dinonaktifkan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'years' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'ustazList' => User::query()->role(Role::Ustaz)->where('is_active', true)->orderBy('name')->get(),
            'santriStatus' => SantriStatus::Aktif,
        ];
    }
}
