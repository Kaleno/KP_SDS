<?php

namespace App\Http\Controllers\Ketua;

use App\Enums\RegistrationStatus;
use App\Http\Requests\Ketua\ApproveSantriRegistrationRequest;
use App\Http\Requests\Ketua\RejectSantriRegistrationRequest;
use App\Models\SantriRegistration;
use App\Services\ApproveSantriRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SantriRegistrationController extends KetuaController
{
    public function __construct(private ApproveSantriRegistration $approver) {}

    public function index(): View
    {
        return view('ketua.registrations.index', [
            'pending' => SantriRegistration::query()
                ->pending()
                ->latest()
                ->get(),
            'recent' => SantriRegistration::query()
                ->where('status', '!=', RegistrationStatus::Pending)
                ->latest('reviewed_at')
                ->limit(20)
                ->get(),
        ]);
    }

    public function show(SantriRegistration $registration): View
    {
        return view('ketua.registrations.show', [
            'registration' => $registration,
        ]);
    }

    public function approve(ApproveSantriRegistrationRequest $request, SantriRegistration $registration): RedirectResponse
    {
        $this->approver->handle($registration, $request->user(), [
            'username' => $request->string('username')->toString(),
            'password' => $request->string('password')->toString(),
            'nis' => $request->string('nis')->toString(),
        ]);

        return redirect()
            ->route('ketua.registrations.index')
            ->with('status', 'Pendaftaran disetujui. Akun santri siap dipakai.');
    }

    public function reject(RejectSantriRegistrationRequest $request, SantriRegistration $registration): RedirectResponse
    {
        $this->approver->reject(
            $registration,
            $request->user(),
            $request->input('rejection_note'),
        );

        return redirect()
            ->route('ketua.registrations.index')
            ->with('status', 'Pendaftaran ditolak.');
    }
}
