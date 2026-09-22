<?php

namespace App\Http\Controllers\Portal;

use App\Enums\SchoolLevel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\UpdateSantriProfileRequest;
use App\Support\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->hasRole(Role::Santri), 403);
        $santri = $user->santriProfile;
        abort_unless($santri, 404);

        return view('portal.profile', [
            'santri' => $santri,
            'schoolLevels' => SchoolLevel::ordered(),
        ]);
    }

    public function update(UpdateSantriProfileRequest $request): RedirectResponse
    {
        $santri = $request->user()->santriProfile;
        abort_unless($santri, 404);

        $data = $request->safe()->only(['parent_name', 'school_level', 'birth_date']);

        if ($request->hasFile('photo')) {
            if ($santri->photo_path) {
                Storage::disk('public')->delete($santri->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('santri-photos', 'public');
        }

        $santri->update($data);

        if ($request->filled('name')) {
            $santri->user->update(['name' => $request->string('name')->toString()]);
        }

        return redirect()->route('portal.profile.edit')->with('status', 'Profil diperbarui.');
    }
}
