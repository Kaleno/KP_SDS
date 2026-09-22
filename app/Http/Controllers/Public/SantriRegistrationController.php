<?php

namespace App\Http\Controllers\Public;

use App\Enums\Gender;
use App\Enums\RegistrationStatus;
use App\Enums\SantriTrack;
use App\Enums\SchoolLevel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreSantriRegistrationRequest;
use App\Models\SantriRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SantriRegistrationController extends Controller
{
    public function create(): View
    {
        return view('public.daftar', [
            'schoolLevels' => SchoolLevel::ordered(),
            'tracks' => SantriTrack::cases(),
            'genders' => Gender::cases(),
        ]);
    }

    public function store(StoreSantriRegistrationRequest $request): RedirectResponse
    {
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('registration-photos', 'public');
        }

        SantriRegistration::query()->create([
            ...$request->safe()->only([
                'name',
                'parent_name',
                'school_level',
                'birth_date',
                'gender',
                'track',
            ]),
            'photo_path' => $photoPath,
            'status' => RegistrationStatus::Pending,
        ]);

        return redirect()
            ->route('daftar.create')
            ->with('status', 'Pendaftaran terkirim. Menunggu persetujuan Ketua DKM.');
    }
}
