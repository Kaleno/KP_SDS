<?php

namespace App\Http\Controllers\Ketua;

use App\Http\Requests\Ketua\SaveKelasRequest;
use App\Models\Halaqah;
use App\Models\User;
use App\Services\SaveKelas;
use App\Support\Role;
use App\Support\WeekDay;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HalaqahController extends KetuaController
{
    public function __construct(private SaveKelas $saveKelas) {}

    public function index(): View
    {
        $kelasList = Halaqah::query()
            ->with(['ustaz', 'schedules' => fn ($query) => $query->where('is_active', true)->orderBy('day_of_week')])
            ->withCount('activeMembers')
            ->orderBy('name')
            ->get();

        return view('ketua.halaqah.index', [
            'kelasList' => $kelasList,
            'days' => WeekDay::labels(),
        ]);
    }

    public function create(): View
    {
        return view('ketua.halaqah.create', $this->formOptions());
    }

    public function store(SaveKelasRequest $request): RedirectResponse
    {
        $this->saveKelas->handle(null, $request->validated());

        return redirect()->route('ketua.halaqah.index')->with('status', 'Kelas disimpan. Semua santri aktif otomatis ikut pembelajaran.');
    }

    public function show(Halaqah $halaqah): RedirectResponse
    {
        return redirect()->route('ketua.halaqah.edit', $halaqah);
    }

    public function edit(Halaqah $halaqah): View
    {
        $halaqah->load(['schedules' => fn ($query) => $query->where('is_active', true)->orderBy('day_of_week')]);

        return view('ketua.halaqah.edit', [
            'halaqah' => $halaqah,
            ...$this->formOptions($halaqah),
        ]);
    }

    public function update(SaveKelasRequest $request, Halaqah $halaqah): RedirectResponse
    {
        $this->saveKelas->handle($halaqah, $request->validated());

        return redirect()->route('ketua.halaqah.index')->with('status', 'Kelas diperbarui.');
    }

    public function toggle(Halaqah $halaqah): RedirectResponse
    {
        $halaqah->update(['is_active' => ! $halaqah->is_active]);

        return back()->with('status', $halaqah->is_active ? 'Kelas diaktifkan.' : 'Kelas dinonaktifkan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(?Halaqah $halaqah = null): array
    {
        $activeSchedules = $halaqah?->schedules ?? collect();
        $first = $activeSchedules->first();

        return [
            'ustazList' => User::query()->role(Role::teaching())->where('is_active', true)->orderBy('name')->get(),
            'days' => WeekDay::labels(),
            'selectedDays' => $activeSchedules->pluck('day_of_week')->map(fn ($day): int => (int) $day)->values()->all(),
            'startTime' => $first ? substr((string) $first->start_time, 0, 5) : '07:00',
            'endTime' => $first ? substr((string) $first->end_time, 0, 5) : '08:30',
        ];
    }
}
