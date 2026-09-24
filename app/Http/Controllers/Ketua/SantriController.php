<?php

namespace App\Http\Controllers\Ketua;

use App\Enums\SantriStatus;
use App\Http\Requests\Ketua\StoreSantriRequest;
use App\Http\Requests\Ketua\UpdateSantriRequest;
use App\Models\SantriProfile;
use App\Models\User;
use App\Services\DeleteSantri;
use App\Services\SantriLifecycle;
use App\Support\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SantriController extends KetuaController
{
    public function __construct(
        private SantriLifecycle $lifecycle,
        private DeleteSantri $deleteSantri,
    ) {}

    public function index(Request $request): View
    {
        $statusFilter = $request->input('status');

        $santriList = SantriProfile::query()
            ->with('user')
            ->when(
                $statusFilter && SantriStatus::tryFrom($statusFilter),
                fn ($query) => $query->where('status', $statusFilter),
            )
            ->orderBy('nis')
            ->get();

        return view('ketua.santri.index', [
            'santriList' => $santriList,
            'statusFilter' => $statusFilter,
            'statuses' => SantriStatus::cases(),
            'details' => $santriList->mapWithKeys(
                fn (SantriProfile $santri): array => [$santri->id => $this->lifecycle->detailPayload($santri)]
            ),
        ]);
    }

    public function create(): View
    {
        return view('ketua.santri.create');
    }

    public function store(StoreSantriRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $status = SantriStatus::from($request->string('status')->toString());

            $user = User::query()->create([
                'name' => $request->string('name')->toString(),
                'username' => $request->string('nis')->toString(),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'password' => $request->string('password')->toString(),
                'is_active' => $status->allowsLogin(),
            ]);
            $user->assignRole(Role::Santri);

            $joinedAt = now()->toDateString();

            SantriProfile::query()->create([
                'user_id' => $user->id,
                'nis' => $request->string('nis')->toString(),
                'gender' => $request->string('gender')->toString(),
                'birth_date' => $request->input('birth_date'),
                'parent_name' => $request->input('parent_name'),
                'address' => $request->input('address'),
                'school_level' => $request->input('school_level'),
                'track' => $request->string('track')->toString(),
                'iqro_level' => $request->string('track')->toString() === 'iqro' ? 1 : null,
                'status' => $status,
                'joined_at' => $joinedAt,
                'graduated_at' => $status === SantriStatus::Lulus ? $joinedAt : null,
                'spp_obligation_from' => $status === SantriStatus::Aktif
                    ? now()->startOfMonth()->toDateString()
                    : null,
                'photo_path' => $this->lifecycle->storePhoto($request->file('photo')),
            ]);
        });

        return redirect()->route('ketua.santri.index')->with('status', 'Santri disimpan.');
    }

    public function edit(SantriProfile $santri): View
    {
        return view('ketua.santri.edit', ['santri' => $santri->load('user')]);
    }

    public function update(UpdateSantriRequest $request, SantriProfile $santri): RedirectResponse
    {
        DB::transaction(function () use ($request, $santri): void {
            $previous = $santri->status;
            $status = SantriStatus::from($request->string('status')->toString());

            $userData = [
                'name' => $request->string('name')->toString(),
                'username' => $request->string('nis')->toString(),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'is_active' => $status->allowsLogin(),
            ];
            if ($request->filled('password')) {
                $userData['password'] = $request->string('password')->toString();
            }
            $santri->user->update($userData);

            $santri->fill($request->safe()->only([
                'nis',
                'gender',
                'birth_date',
                'parent_name',
                'address',
                'school_level',
                'track',
            ]));

            if ($request->hasFile('photo')) {
                $santri->photo_path = $this->lifecycle->storePhoto($request->file('photo'), $santri->photo_path);
            }

            $santri->save();

            if ($santri->track?->value === 'iqro' && ! $santri->iqro_level) {
                $santri->update(['iqro_level' => 1]);
            }

            $this->lifecycle->applyStatus($santri->fresh(), $status, $previous);
        });

        return redirect()->route('ketua.santri.index')->with('status', 'Santri diperbarui.');
    }

    public function resetPassword(SantriProfile $santri): RedirectResponse
    {
        $santri->loadMissing('user');
        $santri->user->update(['password' => 'password']);

        return back()->with('status', "Password {$santri->user->name} direset ke default (password).");
    }

    public function destroy(SantriProfile $santri): RedirectResponse
    {
        $name = $santri->loadMissing('user')->user->name;
        $this->deleteSantri->handle($santri);

        return redirect()
            ->route('ketua.santri.index')
            ->with('status', "Santri {$name} dihapus permanen.");
    }
}
