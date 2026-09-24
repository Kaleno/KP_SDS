<?php

namespace App\Http\Controllers;

use App\Enums\FinanceType;
use App\Enums\Gender;
use App\Enums\SantriStatus;
use App\Models\FinanceEntry;
use App\Models\SantriProfile;
use App\Services\HalaqahReadiness;
use App\Services\OperationalDashboard;
use App\Services\SppService;
use App\Support\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private OperationalDashboard $dashboard,
        private HalaqahReadiness $readiness,
        private SppService $spp,
    ) {}

    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasRole(Role::Santri)) {
            return redirect()->route('portal.home');
        }

        $role = $user->getRoleNames()->first();
        $isKetua = $user->hasRole(Role::Ketua);
        $isPengajar = $user->hasAnyRole(Role::teaching());

        return view('dashboard', [
            'roleLabel' => $role ? Role::label($role) : 'Pengguna',
            'isSuperAdmin' => $user->hasRole(Role::SuperAdmin),
            'isKetua' => $isKetua,
            'isUstaz' => $isPengajar,
            'overview' => $isPengajar
                ? $this->dashboard->for($user)
                : ($isKetua ? $this->dashboard->weekRecap() : null),
            'readiness' => $isKetua ? $this->readiness->snapshot() : null,
            'sppSummary' => $isKetua ? $this->spp->summary() : null,
            'santriCensus' => $isKetua ? $this->santriCensus() : null,
            'finance' => $isKetua ? $this->financeTotals() : null,
        ]);
    }

    /**
     * Perempuan dan laki-laki dihitung dari santri aktif saja.
     *
     * @return array{aktif: int, perempuan: int, lakiLaki: int, lulus: int}
     */
    private function santriCensus(): array
    {
        $counts = [
            'aktif' => 0,
            'perempuan' => 0,
            'lakiLaki' => 0,
            'lulus' => 0,
        ];

        $rows = SantriProfile::query()
            ->selectRaw('status, gender, COUNT(*) as total')
            ->groupBy('status', 'gender')
            ->get();

        foreach ($rows as $row) {
            $total = (int) $row->total;
            $status = $row->status;
            $gender = $row->gender;

            if ($status === SantriStatus::Aktif) {
                $counts['aktif'] += $total;

                if ($gender === Gender::Perempuan) {
                    $counts['perempuan'] += $total;
                }

                if ($gender === Gender::LakiLaki) {
                    $counts['lakiLaki'] += $total;
                }
            }

            if ($status === SantriStatus::Lulus) {
                $counts['lulus'] += $total;
            }
        }

        return $counts;
    }

    /**
     * Saldo, pemasukan, dan pengeluaran sepanjang catatan kas.
     *
     * @return array{saldo: int, pemasukan: int, pengeluaran: int}
     */
    private function financeTotals(): array
    {
        $pemasukan = (int) FinanceEntry::query()->where('type', FinanceType::Pemasukan)->sum('amount');
        $pengeluaran = (int) FinanceEntry::query()->where('type', FinanceType::Pengeluaran)->sum('amount');

        return [
            'saldo' => $pemasukan - $pengeluaran,
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
        ];
    }
}
