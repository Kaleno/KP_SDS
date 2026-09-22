<?php

namespace App\Http\Controllers\Ketua;

use App\Http\Requests\Ketua\StoreSantriRequest;
use App\Http\Requests\Ketua\UpdateSantriRequest;
use App\Models\SantriProfile;
use App\Models\User;
use App\Services\HalaqahMembershipService;
use App\Support\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SantriController extends KetuaController
{
    public function __construct(private HalaqahMembershipService $memberships) {}

    public function index(): View
    {
        return view('ketua.santri.index', [
            'santriList' => SantriProfile::query()->with('user')->orderBy('nis')->get(),
        ]);
    }

    public function create(): View
    {
        return view('ketua.santri.create');
    }

    public function store(StoreSantriRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $user = User::query()->create([
                'name' => $request->string('name')->toString(),
                'username' => $request->string('nis')->toString(),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'password' => $request->string('password')->toString(),
                'is_active' => true,
            ]);
            $user->assignRole(Role::Santri);

            $santri = SantriProfile::query()->create([
                'user_id' => $user->id,
                'nis' => $request->string('nis')->toString(),
                'gender' => $request->string('gender')->toString(),
                'birth_date' => $request->input('birth_date'),
                'parent_name' => $request->input('parent_name'),
                'school_level' => $request->input('school_level'),
                'track' => $request->string('track')->toString(),
                'iqro_level' => $request->string('track')->toString() === 'iqro' ? 1 : null,
                'status' => $request->string('status')->toString(),
            ]);

            $this->memberships->syncSantriAcrossActiveClasses($santri);
        });

        return redirect()->route('ketua.santri.index')->with('status', 'Santri disimpan.');
    }

    public function edit(SantriProfile $santri): View
    {
        return view('ketua.santri.edit', ['santri' => $santri->load('user')]);
    }

    public function update(UpdateSantriRequest $request, SantriProfile $santri): RedirectResponse
    {
        DB::transaction(function () use ($request, $santri) {
            $userData = [
                'name' => $request->string('name')->toString(),
                'username' => $request->string('nis')->toString(),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
            ];
            if ($request->filled('password')) {
                $userData['password'] = $request->string('password')->toString();
            }
            $santri->user->update($userData);
            $santri->update($request->safe()->only([
                'nis',
                'gender',
                'birth_date',
                'status',
                'parent_name',
                'school_level',
                'track',
            ]));
            if ($santri->track?->value === 'iqro' && ! $santri->iqro_level) {
                $santri->update(['iqro_level' => 1]);
            }
            $this->memberships->syncSantriAcrossActiveClasses($santri->fresh());
        });

        return redirect()->route('ketua.santri.index')->with('status', 'Santri diperbarui.');
    }
}
