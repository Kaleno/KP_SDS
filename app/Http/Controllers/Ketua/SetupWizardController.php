<?php

namespace App\Http\Controllers\Ketua;

use App\Enums\Gender;
use App\Http\Requests\Ketua\PrepareHalaqahRequest;
use App\Models\AcademicYear;
use App\Models\Location;
use App\Models\SantriProfile;
use App\Models\User;
use App\Services\HalaqahReadiness;
use App\Services\PrepareHalaqah;
use App\Support\Role;
use App\Support\WeekDay;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SetupWizardController extends KetuaController
{
    public function __construct(
        private HalaqahReadiness $readiness,
        private PrepareHalaqah $prepare,
    ) {}

    public function create(): View
    {
        return view('ketua.setup.show', [
            'readiness' => $this->readiness->snapshot(),
            'years' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'locations' => Location::query()->orderBy('name')->get(),
            'ustazList' => User::query()->role(Role::Ustaz)->where('is_active', true)->orderBy('name')->get(),
            'santriList' => SantriProfile::query()->aktif()->with('user')->orderBy('nis')->get(),
            'days' => WeekDay::labels(),
            'genders' => Gender::cases(),
        ]);
    }

    public function store(PrepareHalaqahRequest $request): RedirectResponse
    {
        $halaqah = $this->prepare->handle($request->validated());

        return redirect()
            ->route('ketua.halaqah.show', $halaqah)
            ->with('status', 'Halaqah siap. Pengajar bisa membuka absensi pada hari jadwal yang dipilih.');
    }
}
