<?php

namespace App\Http\Controllers\Ketua;

use App\Enums\EducationLevel;
use App\Http\Requests\Ketua\StoreUstazRequest;
use App\Http\Requests\Ketua\UpdateUstazRequest;
use App\Models\User;
use App\Support\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UstazController extends KetuaController
{
    public function index(): View
    {
        $ustazList = User::query()
            ->role(Role::teaching())
            ->orderBy('name')
            ->get();

        return view('ketua.ustaz.index', [
            'ustazList' => $ustazList,
            'details' => $ustazList->mapWithKeys(
                fn (User $ustaz): array => [$ustaz->id => $this->detailPayload($ustaz)]
            ),
        ]);
    }

    public function create(): View
    {
        return view('ketua.ustaz.create', [
            'educationLevels' => EducationLevel::ordered(),
        ]);
    }

    public function store(StoreUstazRequest $request): RedirectResponse
    {
        $ustaz = User::query()->create([
            ...$request->safe()->only([
                'name',
                'nip',
                'username',
                'email',
                'phone',
                'birth_date',
                'address',
                'education_level',
            ]),
            'is_active' => $request->boolean('is_active'),
            'password' => $request->string('password')->toString(),
            'photo_path' => $this->storePhoto($request->file('photo')),
        ]);
        $ustaz->assignRole($request->string('teaching_role')->toString());

        return redirect()->route('ketua.ustaz.index')->with('status', 'Akun ustadz disimpan.');
    }

    public function edit(User $ustaz): View
    {
        abort_unless($ustaz->hasAnyRole(Role::teaching()), 404);

        return view('ketua.ustaz.edit', [
            'ustaz' => $ustaz,
            'educationLevels' => EducationLevel::ordered(),
        ]);
    }

    public function update(UpdateUstazRequest $request, User $ustaz): RedirectResponse
    {
        abort_unless($ustaz->hasAnyRole(Role::teaching()), 404);

        $data = $request->safe()->only([
            'name',
            'nip',
            'username',
            'email',
            'phone',
            'birth_date',
            'address',
            'education_level',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->filled('password')) {
            $data['password'] = $request->string('password')->toString();
        }

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $this->storePhoto($request->file('photo'), $ustaz->photo_path);
        }

        $ustaz->update($data);
        $ustaz->syncRoles([$request->string('teaching_role')->toString()]);

        return redirect()->route('ketua.ustaz.index')->with('status', 'Akun ustadz diperbarui.');
    }

    public function toggle(User $ustaz): RedirectResponse
    {
        abort_unless($ustaz->hasAnyRole(Role::teaching()), 404);

        $ustaz->update(['is_active' => ! $ustaz->is_active]);

        return back()->with('status', $ustaz->is_active ? 'Ustadz diaktifkan.' : 'Ustadz dinonaktifkan.');
    }

    public function resetPassword(User $ustaz): RedirectResponse
    {
        abort_unless($ustaz->hasAnyRole(Role::teaching()), 404);

        $ustaz->update(['password' => 'password']);

        return back()->with('status', "Password {$ustaz->name} direset ke default (password).");
    }

    public function destroy(User $ustaz): RedirectResponse
    {
        abort_unless($ustaz->hasAnyRole(Role::teaching()), 404);

        $name = $ustaz->name;
        $ustaz->update(['is_active' => false]);
        $ustaz->delete();

        return redirect()
            ->route('ketua.ustaz.index')
            ->with('status', "Ustadz {$name} dihapus dari daftar. Riwayat tetap tersimpan.");
    }

    private function storePhoto(?UploadedFile $photo, ?string $existingPath = null): ?string
    {
        if ($photo === null) {
            return $existingPath;
        }

        if ($existingPath) {
            Storage::disk('public')->delete($existingPath);
        }

        return $photo->store('ustadz-photos', 'public');
    }

    /**
     * @return array<string, mixed>
     */
    private function detailPayload(User $ustaz): array
    {
        return [
            'id' => $ustaz->id,
            'name' => $ustaz->name,
            'nip' => $ustaz->nip ?: '—',
            'photo_url' => $ustaz->photoUrl(),
            'birth_date' => $ustaz->birth_date?->format('d-m-Y') ?? '—',
            'phone' => $ustaz->phone ?: '—',
            'address' => $ustaz->address ?: '—',
            'education_level' => $ustaz->education_level?->label() ?? '—',
            'role' => Role::label($ustaz->getRoleNames()->first() ?? ''),
            'status' => $ustaz->is_active ? 'Aktif' : 'Tidak aktif',
            'username' => $ustaz->username,
        ];
    }
}
