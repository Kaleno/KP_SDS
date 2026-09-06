<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreKetuaRequest;
use App\Models\User;
use App\Support\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KetuaAccountController extends Controller
{
    public function index(): View
    {
        $ketuaAccounts = User::query()
            ->role(Role::Ketua)
            ->orderBy('name')
            ->get();

        return view('super-admin.ketua.index', [
            'ketuaAccounts' => $ketuaAccounts,
        ]);
    }

    public function store(StoreKetuaRequest $request): RedirectResponse
    {
        $ketua = User::query()->create([
            'name' => $request->string('name')->toString(),
            'username' => $request->string('username')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
            'is_active' => true,
        ]);

        $ketua->assignRole(Role::Ketua);

        return redirect()
            ->route('super-admin.ketua.index')
            ->with('status', 'Akun Ketua berhasil dibuat.');
    }

    public function toggle(User $ketua): RedirectResponse
    {
        abort_unless($ketua->hasRole(Role::Ketua), 404);

        $ketua->update([
            'is_active' => ! $ketua->is_active,
        ]);

        $message = $ketua->is_active
            ? "Akun {$ketua->name} diaktifkan."
            : "Akun {$ketua->name} dinonaktifkan.";

        return redirect()
            ->route('super-admin.ketua.index')
            ->with('status', $message);
    }
}
