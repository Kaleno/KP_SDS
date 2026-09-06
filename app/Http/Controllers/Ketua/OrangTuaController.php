<?php

namespace App\Http\Controllers\Ketua;

use App\Http\Requests\Ketua\AttachChildRequest;
use App\Http\Requests\Ketua\StoreOrangTuaRequest;
use App\Http\Requests\Ketua\UpdateOrangTuaRequest;
use App\Models\SantriProfile;
use App\Models\User;
use App\Support\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrangTuaController extends KetuaController
{
    public function index(): View
    {
        return view('ketua.orang-tua.index', [
            'parents' => User::query()->role(Role::OrangTua)->with('children.user')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('ketua.orang-tua.create');
    }

    public function store(StoreOrangTuaRequest $request): RedirectResponse
    {
        $parent = User::query()->create([
            ...$request->safe()->only(['name', 'username', 'email', 'phone']),
            'password' => $request->string('password')->toString(),
            'is_active' => true,
        ]);
        $parent->assignRole(Role::OrangTua);

        return redirect()->route('ketua.orang-tua.edit', $parent)->with('status', 'Akun orang tua dibuat. Tautkan anak lewat NIS.');
    }

    public function edit(User $orangTua): View
    {
        abort_unless($orangTua->hasRole(Role::OrangTua), 404);

        return view('ketua.orang-tua.edit', [
            'parent' => $orangTua->load('children.user'),
        ]);
    }

    public function update(UpdateOrangTuaRequest $request, User $orangTua): RedirectResponse
    {
        abort_unless($orangTua->hasRole(Role::OrangTua), 404);

        $data = $request->safe()->only(['name', 'username', 'email', 'phone']);
        if ($request->filled('password')) {
            $data['password'] = $request->string('password')->toString();
        }
        $orangTua->update($data);

        return back()->with('status', 'Akun orang tua diperbarui.');
    }

    public function attachChild(AttachChildRequest $request, User $orangTua): RedirectResponse
    {
        abort_unless($orangTua->hasRole(Role::OrangTua), 404);

        $santri = SantriProfile::query()->where('nis', $request->string('nis')->toString())->firstOrFail();

        $orangTua->children()->syncWithoutDetaching([$santri->id]);

        return back()->with('status', "Anak {$santri->user->name} (NIS {$santri->nis}) ditautkan.");
    }

    public function detachChild(User $orangTua, SantriProfile $santri): RedirectResponse
    {
        abort_unless($orangTua->hasRole(Role::OrangTua), 404);

        $orangTua->children()->detach($santri->id);

        return back()->with('status', 'Tautan anak dilepas.');
    }

    public function toggle(User $orangTua): RedirectResponse
    {
        abort_unless($orangTua->hasRole(Role::OrangTua), 404);

        $orangTua->update(['is_active' => ! $orangTua->is_active]);

        return back()->with('status', $orangTua->is_active ? 'Akun diaktifkan.' : 'Akun dinonaktifkan.');
    }
}
