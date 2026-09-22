<?php

namespace App\Http\Controllers\Ops;

use App\Enums\SantriTrack;
use App\Http\Controllers\Controller;
use App\Models\SantriProfile;
use App\Support\OperationalAccess;
use App\Support\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SantriTrackController extends Controller
{
    public function __construct(private OperationalAccess $access) {}

    public function update(Request $request, SantriProfile $santri): RedirectResponse
    {
        abort_unless($request->user()->hasRole(Role::KetuaPengajar) || $request->user()->hasRole(Role::Ketua), 403);
        $this->access->assertSantri($request->user(), $santri);

        $validated = $request->validate([
            'track' => ['required', Rule::enum(SantriTrack::class)],
        ]);

        $data = ['track' => $validated['track']];
        if ($validated['track'] === SantriTrack::Iqro->value && ! $santri->iqro_level) {
            $data['iqro_level'] = 1;
        }

        $santri->update($data);

        return back()->with('status', 'Jalur santri diperbarui.');
    }
}
