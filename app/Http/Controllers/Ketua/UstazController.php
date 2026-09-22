<?php

namespace App\Http\Controllers\Ketua;

use App\Http\Requests\Ketua\StoreUstazRequest;
use App\Http\Requests\Ketua\UpdateUstazRequest;
use App\Models\User;
use App\Support\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UstazController extends KetuaController
{
    public function index(): View
    {
        return view('ketua.ustaz.index', [
            'ustazList' => User::query()
                ->role(Role::teaching())
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('ketua.ustaz.create');
    }

    public function store(StoreUstazRequest $request): RedirectResponse
    {
        $ustaz = User::query()->create([
            ...$request->safe()->only(['name', 'username', 'email', 'phone']),
            'password' => $request->string('password')->toString(),
            'is_active' => true,
        ]);
        $ustaz->assignRole($request->string('teaching_role')->toString());

        return redirect()->route('ketua.ustaz.index')->with('status', 'Akun pengajar dibuat.');
    }

    public function edit(User $ustaz): View
    {
        abort_unless($ustaz->hasAnyRole(Role::teaching()), 404);

        return view('ketua.ustaz.edit', ['ustaz' => $ustaz]);
    }

    public function update(UpdateUstazRequest $request, User $ustaz): RedirectResponse
    {
        abort_unless($ustaz->hasAnyRole(Role::teaching()), 404);

        $data = $request->safe()->only(['name', 'username', 'email', 'phone']);
        if ($request->filled('password')) {
            $data['password'] = $request->string('password')->toString();
        }
        $ustaz->update($data);
        $ustaz->syncRoles([$request->string('teaching_role')->toString()]);

        return redirect()->route('ketua.ustaz.index')->with('status', 'Akun pengajar diperbarui.');
    }

    public function toggle(User $ustaz): RedirectResponse
    {
        abort_unless($ustaz->hasAnyRole(Role::teaching()), 404);

        $ustaz->update(['is_active' => ! $ustaz->is_active]);

        return back()->with('status', $ustaz->is_active ? 'Pengajar diaktifkan.' : 'Pengajar dinonaktifkan.');
    }
}
