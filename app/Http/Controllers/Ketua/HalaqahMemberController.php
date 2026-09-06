<?php

namespace App\Http\Controllers\Ketua;

use App\Http\Requests\Ketua\StoreHalaqahMemberRequest;
use App\Http\Requests\Ketua\MutateHalaqahMemberRequest;
use App\Models\Halaqah;
use App\Models\HalaqahMember;
use App\Models\SantriProfile;
use App\Services\HalaqahMembershipService;
use Illuminate\Http\RedirectResponse;

class HalaqahMemberController extends KetuaController
{
    public function store(StoreHalaqahMemberRequest $request, Halaqah $halaqah, HalaqahMembershipService $memberships): RedirectResponse
    {
        $santri = SantriProfile::query()->findOrFail($request->integer('santri_id'));
        $memberships->add($halaqah, $santri, $request->string('started_at')->toString());

        return back()->with('status', "{$santri->loadMissing('user')->user->name} masuk halaqah.");
    }

    public function mutate(MutateHalaqahMemberRequest $request, Halaqah $halaqah, HalaqahMember $member, HalaqahMembershipService $memberships): RedirectResponse
    {
        abort_unless($member->halaqah_id === $halaqah->id, 404);

        $target = Halaqah::query()->findOrFail($request->integer('target_halaqah_id'));
        $memberships->mutate(
            $member,
            $target,
            $request->string('moved_at')->toString(),
            $request->input('mutation_note'),
        );

        return redirect()
            ->route('ketua.halaqah.show', $target)
            ->with('status', "Santri dipindahkan ke {$target->name}.");
    }

    public function destroy(Halaqah $halaqah, HalaqahMember $member, HalaqahMembershipService $memberships): RedirectResponse
    {
        abort_unless($member->halaqah_id === $halaqah->id, 404);

        $memberships->end($member, now()->toDateString(), 'Dikeluarkan dari halaqah');

        return back()->with('status', 'Keanggotaan ditutup.');
    }
}
