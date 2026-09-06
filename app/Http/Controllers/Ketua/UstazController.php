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
            'ustazList' => User::query()->role(Role::Ustaz)->orderBy('name')->get(),
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
        $ustaz->assignRole(Role::Ustaz);

        return redirect()->route('ketua.ustaz.index')->with('status', 'Akun ustaz dibuat.');
    }

    public function edit(User $ustaz): View
    {
        abort_unless($ustaz->hasRole(Role::Ustaz), 404);

        return view('ketua.ustaz.edit', ['ustaz' => $ustaz]);
    }

    public function update(UpdateUstazRequest $request, User $ustaz): RedirectResponse
    {
        abort_unless($ustaz->hasRole(Role::Ustaz), 404);

        $data = $request->safe()->only(['name', 'username', 'email', 'phone']);
        if ($request->filled('password')) {
            $data['password'] = $request->string('password')->toString();
        }
        $ustaz->update($data);

        return redirect()->route('ketua.ustaz.index')->with('status', 'Akun ustaz diperbarui.');
    }

    public function toggle(User $ustaz): RedirectResponse
    {
        abort_unless($ustaz->hasRole(Role::Ustaz), 404);

        $ustaz->update(['is_active' => ! $ustaz->is_active]);

        return back()->with('status', $ustaz->is_active ? 'Ustaz diaktifkan.' : 'Ustaz dinonaktifkan.');
    }
}
