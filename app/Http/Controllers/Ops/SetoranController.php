<?php

namespace App\Http\Controllers\Ops;

use App\Enums\SetoranStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ops\StoreSetoranRequest;
use App\Http\Requests\Ops\UpdateSetoranRequest;
use App\Models\HafalanSetoran;
use App\Models\QuranSurah;
use App\Models\SantriProfile;
use App\Models\User;
use App\Support\DateQuery;
use App\Support\OperationalAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SetoranController extends Controller
{
    public function __construct(private OperationalAccess $access) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $halaqahIds = $this->access->halaqahQuery($user)->aktif()->pluck('id');

        $query = HafalanSetoran::query()
            ->with(['santri.user', 'surah', 'halaqah'])
            ->whereIn('halaqah_id', $halaqahIds);

        if ($request->filled('quran_surah_id')) {
            $query->where('quran_surah_id', $request->integer('quran_surah_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        $from = DateQuery::ymd($request->input('date_from'));
        $to = DateQuery::ymd($request->input('date_to'));
        if ($from && $to && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        if ($from) {
            $query->whereDate('setoran_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('setoran_date', '<=', $to);
        }
        if ($request->filled('santri_id')) {
            $query->where('santri_id', $request->integer('santri_id'));
        }

        $santriOptions = SantriProfile::query()
            ->with('user')
            ->whereIn('id', $this->access->guidedMemberQuery($user)->pluck('santri_id'))
            ->get()
            ->sortBy(fn (SantriProfile $santri) => $santri->user->name);

        return view('ops.setoran.index', [
            'setoran' => $query->orderByDesc('setoran_date')->orderByDesc('id')->paginate(20)->withQueryString(),
            'surahs' => QuranSurah::query()->orderBy('id')->get(),
            'statuses' => SetoranStatus::cases(),
            'santriOptions' => $santriOptions,
            'filters' => [
                'quran_surah_id' => $request->input('quran_surah_id'),
                'status' => $request->input('status'),
                'date_from' => $from ?? $request->input('date_from'),
                'date_to' => $to ?? $request->input('date_to'),
                'santri_id' => $request->input('santri_id'),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        return view('ops.setoran.create', $this->formData($request->user()));
    }

    public function store(StoreSetoranRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $member = $this->access
            ->guidedMemberQuery($request->user())
            ->where('santri_id', $validated['santri_id'])
            ->first();

        if (! $member) {
            throw ValidationException::withMessages([
                'santri_id' => 'Santri tidak ada di halaqah yang boleh Anda isi.',
            ]);
        }

        HafalanSetoran::query()->create([
            ...$validated,
            'halaqah_id' => $member->halaqah_id,
            'academic_year_id' => $member->academic_year_id,
            'ustaz_user_id' => $request->user()->id,
        ]);

        return redirect()->route('ops.setoran.index')->with('status', 'Setoran disimpan.');
    }

    public function edit(Request $request, HafalanSetoran $setoran): View
    {
        $setoran->load(['halaqah', 'santri.user']);
        $this->access->assertHalaqah($request->user(), $setoran->halaqah);

        return view('ops.setoran.edit', $this->formData($request->user(), $setoran));
    }

    public function update(UpdateSetoranRequest $request, HafalanSetoran $setoran): RedirectResponse
    {
        $setoran->load('halaqah');
        $this->access->assertHalaqah($request->user(), $setoran->halaqah);

        $setoran->update($request->validated());

        return redirect()->route('ops.setoran.index')->with('status', 'Setoran dikoreksi.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(User $user, ?HafalanSetoran $setoran = null): array
    {
        $members = $this->access
            ->guidedMemberQuery($user)
            ->with(['santri.user', 'halaqah'])
            ->get()
            ->sortBy(fn ($member) => $member->santri->user->name);

        return [
            'members' => $members,
            'surahs' => QuranSurah::query()->orderBy('id')->get(),
            'statuses' => SetoranStatus::cases(),
            'setoran' => $setoran,
        ];
    }
}
