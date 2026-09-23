<?php

namespace App\Http\Controllers\Ops;

use App\Enums\AttendanceStatus;
use App\Enums\SetoranCategory;
use App\Enums\SetoranStatus;
use App\Enums\SetoranSubtype;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ops\StoreSetoranRequest;
use App\Http\Requests\Ops\UpdateSetoranRequest;
use App\Models\AttendanceSession;
use App\Models\HafalanSetoran;
use App\Models\QuranJuz;
use App\Models\QuranSurah;
use App\Models\SantriProfile;
use App\Models\User;
use App\Support\DateQuery;
use App\Support\OperationalAccess;
use App\Support\QuranCatalog;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SetoranController extends Controller
{
    public function __construct(
        private OperationalAccess $access,
    ) {}

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
        if ($request->filled('subtype')) {
            $query->where('subtype', $request->input('subtype'));
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

        $today = now()->toDateString();
        $weekFrom = now()->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
        $monthFrom = now()->copy()->startOfMonth()->toDateString();
        $activePeriod = 'all';
        if ($from && $to) {
            $activePeriod = match (true) {
                $from === $today && $to === $today => 'today',
                $from === $weekFrom && $to === $today => 'week',
                $from === $monthFrom && $to === $today => 'month',
                default => 'custom',
            };
        } elseif ($from || $to) {
            $activePeriod = 'custom';
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
                'subtype' => $request->input('subtype'),
            ],
            'activePeriod' => $activePeriod,
            'today' => $today,
            'weekFrom' => $weekFrom,
            'monthFrom' => $monthFrom,
        ]);
    }

    public function create(Request $request): View
    {
        return view('ops.setoran.create', $this->formData($request->user(), session: $this->resolvedSession($request)));
    }

    public function store(StoreSetoranRequest $request): RedirectResponse
    {
        $validated = $request->safe()->except(['sesi']);
        $session = $this->resolvedSession($request);
        $memberQuery = $this->access
            ->guidedMemberQuery($request->user())
            ->where('santri_id', $validated['santri_id']);

        if ($session) {
            $memberQuery->where('halaqah_id', $session->schedule->halaqah_id);
        }

        $member = $memberQuery->with('santri')->first();

        if (! $member) {
            throw ValidationException::withMessages([
                'santri_id' => 'Santri tidak ada di halaqah yang boleh Anda isi.',
            ]);
        }

        if ($session) {
            $hadir = $this->hadirSantriIds($request->user(), $session);
            $santriId = (int) $validated['santri_id'];
            if (! $hadir->contains($santriId)) {
                throw ValidationException::withMessages([
                    'santri_id' => 'Hanya santri yang berstatus hadir di sesi ini.',
                ]);
            }

            $validated['setoran_date'] = $session->session_date->toDateString();
        }

        $subtype = SetoranSubtype::from($validated['subtype']);
        $payload = $this->normalizedPayload($validated, $subtype);

        HafalanSetoran::query()->create([
            ...$payload,
            'halaqah_id' => $member->halaqah_id,
            'academic_year_id' => $member->academic_year_id,
            'ustaz_user_id' => $request->user()->id,
        ]);

        if ($subtype === SetoranSubtype::Iqro && isset($payload['iqro_level'])) {
            $member->santri->update(['iqro_level' => (int) $payload['iqro_level']]);
        }

        if ($session) {
            return redirect()
                ->route('ops.setoran.create', ['sesi' => $session->id])
                ->with('status', 'Setoran disimpan. Bisa lanjut santri atau jenis setoran lain.');
        }

        return redirect()->route('ops.setoran.index')->with('status', 'Setoran disimpan.');
    }

    public function edit(Request $request, HafalanSetoran $setoran): View
    {
        $setoran->load(['halaqah', 'santri.user', 'surah']);
        $this->access->assertHalaqah($request->user(), $setoran->halaqah);

        return view('ops.setoran.edit', $this->formData($request->user(), $setoran));
    }

    public function update(UpdateSetoranRequest $request, HafalanSetoran $setoran): RedirectResponse
    {
        $setoran->load(['halaqah', 'santri']);
        $this->access->assertHalaqah($request->user(), $setoran->halaqah);

        $validated = $request->validated();
        $subtype = SetoranSubtype::from($validated['subtype']);
        $payload = $this->normalizedPayload($validated, $subtype);

        $setoran->update($payload);

        if ($subtype === SetoranSubtype::Iqro && isset($payload['iqro_level'])) {
            $setoran->santri->update(['iqro_level' => (int) $payload['iqro_level']]);
        }

        return redirect()->route('ops.setoran.index')->with('status', 'Setoran dikoreksi.');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function normalizedPayload(array $validated, SetoranSubtype $subtype): array
    {
        $validated['category'] = $subtype->category()->value;
        $validated['subtype'] = $subtype->value;
        $validated['activity_type'] = $subtype->activityType();

        return match ($subtype) {
            SetoranSubtype::Iqro => [
                ...$validated,
                'quran_surah_id' => null,
                'ayah_start' => null,
                'ayah_end' => null,
                'doa_name' => null,
            ],
            SetoranSubtype::Doa => [
                ...$validated,
                'quran_surah_id' => null,
                'ayah_start' => null,
                'ayah_end' => null,
                'iqro_level' => null,
                'iqro_page' => null,
            ],
            SetoranSubtype::Alquran, SetoranSubtype::Juz30 => [
                ...$validated,
                'iqro_level' => null,
                'iqro_page' => null,
                'doa_name' => null,
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(User $user, ?HafalanSetoran $setoran = null, ?AttendanceSession $session = null): array
    {
        $members = $this->access
            ->guidedMemberQuery($user)
            ->with(['santri.user', 'halaqah'])
            ->get()
            ->unique('santri_id')
            ->sortBy(fn ($member) => $member->santri->user->name);

        if ($session) {
            $hadir = $this->hadirSantriIds($user, $session);
            $members = $members
                ->filter(fn ($member) => $hadir->contains((int) $member->santri_id))
                ->values();
        }

        $defaultCategory = old('category', $setoran?->category?->value ?? SetoranCategory::Bacaan->value);
        $defaultSubtype = old('subtype', $setoran?->subtype?->value
            ?? ($setoran?->isIqro() ? SetoranSubtype::Iqro->value : SetoranSubtype::Alquran->value));

        $juz30 = QuranJuz::query()->find(30);
        $allSurahs = QuranSurah::query()->orderBy('id')->get();
        $juz30Surahs = $juz30
            ? $allSurahs->whereBetween('id', [(int) $juz30->start_surah_id, (int) $juz30->end_surah_id])->values()
            : collect();

        $santriIds = $members->pluck('santri_id');

        return [
            'members' => $members,
            'surahs' => $allSurahs,
            'juz30Surahs' => $juz30Surahs,
            'surahPickerItems' => QuranCatalog::surahPickerItems($allSurahs, withJuz: true),
            'juz30PickerItems' => QuranCatalog::surahPickerItems($juz30Surahs, withJuz: true),
            'statuses' => SetoranStatus::cases(),
            'categories' => SetoranCategory::cases(),
            'subtypes' => SetoranSubtype::cases(),
            'setoran' => $setoran,
            'session' => $session,
            'defaultCategory' => $defaultCategory,
            'defaultSubtype' => $defaultSubtype,
            'nextAyahBySantri' => $setoran ? [] : $this->nextAyahBySantri($santriIds),
            'continueBySantri' => $setoran ? [] : $this->continueBySantri($santriIds, $juz30),
            'ayahToJuz' => QuranCatalog::ayahToJuzMap(),
        ];
    }

    /**
     * Lanjutan setoran terakhir per santri & subtipe (untuk prefill form hari berikutnya).
     *
     * @param  Collection<int, mixed>  $santriIds
     * @return array<int, array<string, array<string, mixed>>>
     */
    private function continueBySantri(Collection $santriIds, ?QuranJuz $juz30): array
    {
        $ids = $santriIds->map(fn ($id): int => (int) $id)->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return [];
        }

        $rows = HafalanSetoran::query()
            ->with(['surah:id,name_id,ayah_count'])
            ->whereIn('santri_id', $ids)
            ->whereNotNull('subtype')
            ->orderByDesc('setoran_date')
            ->orderByDesc('id')
            ->get();

        $latest = [];
        foreach ($rows as $row) {
            $key = (int) $row->santri_id.'|'.($row->subtype?->value ?? '');
            if ($key === '|' || isset($latest[$key])) {
                continue;
            }
            $latest[$key] = $row;
        }

        $map = [];
        foreach ($latest as $row) {
            $santriId = (int) $row->santri_id;
            $subtype = $row->subtype;
            if (! $subtype) {
                continue;
            }

            $payload = match ($subtype) {
                SetoranSubtype::Iqro => $this->continueIqro($row),
                SetoranSubtype::Alquran => $this->continueQuran($row, maxSurahId: 114),
                SetoranSubtype::Juz30 => $this->continueQuran(
                    $row,
                    minSurahId: (int) ($juz30?->start_surah_id ?? 78),
                    maxSurahId: (int) ($juz30?->end_surah_id ?? 114),
                ),
                SetoranSubtype::Doa => $row->doa_name
                    ? [
                        'doa_name' => (string) $row->doa_name,
                        'hint' => 'Terakhir: '.($row->doa_name ?: 'Doa'),
                    ]
                    : null,
            };

            if ($payload !== null) {
                $map[$santriId][$subtype->value] = $payload;
            }
        }

        return $map;
    }

    /**
     * @return array{iqro_level: int, iqro_page: int, hint: string}
     */
    private function continueIqro(HafalanSetoran $row): array
    {
        $level = (int) $row->iqro_level;
        $page = (int) $row->iqro_page + 1;
        if ($page > 100) {
            $level = min(6, $level + 1);
            $page = 1;
        }

        return [
            'iqro_level' => $level,
            'iqro_page' => $page,
            'hint' => 'Lanjutan: Iqro '.$level.' hlm. '.$page.' (setelah hlm. '.$row->iqro_page.')',
        ];
    }

    /**
     * @return array{quran_surah_id: int, ayah_start: int, ayah_max: int, juz: int|null, surah_label: string, hint: string}|null
     */
    private function continueQuran(HafalanSetoran $row, int $minSurahId = 1, int $maxSurahId = 114): ?array
    {
        if ($row->quran_surah_id === null || $row->ayah_end === null) {
            return null;
        }

        $surahId = (int) $row->quran_surah_id;
        $ayahEnd = (int) $row->ayah_end;
        $max = (int) ($row->surah?->ayah_count ?? 0);
        if ($max < 1) {
            return null;
        }

        if ($ayahEnd < $max) {
            $nextSurahId = $surahId;
            $nextAyah = $ayahEnd + 1;
            $ayahMax = $max;
            $surahName = $row->surah?->name_id ?? 'Surat';
        } else {
            $nextSurahId = min($maxSurahId, $surahId + 1);
            if ($nextSurahId < $minSurahId) {
                $nextSurahId = $minSurahId;
            }
            $next = QuranSurah::query()->find($nextSurahId);
            if (! $next || $nextSurahId === $surahId) {
                $nextSurahId = $surahId;
                $nextAyah = 1;
                $ayahMax = $max;
                $surahName = $row->surah?->name_id ?? 'Surat';
            } else {
                $nextAyah = 1;
                $ayahMax = (int) $next->ayah_count;
                $surahName = $next->name_id;
            }
        }

        $juz = QuranCatalog::juzForAyah($nextSurahId, $nextAyah);

        return [
            'quran_surah_id' => $nextSurahId,
            'ayah_start' => $nextAyah,
            'ayah_max' => $ayahMax,
            'juz' => $juz,
            'surah_label' => $nextSurahId.'. '.$surahName,
            'hint' => 'Lanjutan: '.$surahName.' ayat '.$nextAyah.($juz ? ' (Juz '.$juz.')' : ''),
        ];
    }

    /**
     * @param  Collection<int, mixed>  $santriIds
     * @return array<int, array<int, int>>
     */
    private function nextAyahBySantri(Collection $santriIds): array
    {
        $ids = $santriIds->map(fn ($id): int => (int) $id)->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return [];
        }

        $latest = HafalanSetoran::query()
            ->with(['surah:id,ayah_count'])
            ->whereIn('santri_id', $ids)
            ->whereNotNull('quran_surah_id')
            ->whereIn('subtype', [SetoranSubtype::Alquran->value, SetoranSubtype::Juz30->value])
            ->orderByDesc('setoran_date')
            ->orderByDesc('id')
            ->get(['id', 'santri_id', 'quran_surah_id', 'ayah_end', 'setoran_date', 'subtype'])
            ->unique(fn (HafalanSetoran $row): string => $row->santri_id.'-'.$row->subtype?->value.'-'.$row->quran_surah_id);

        $map = [];
        foreach ($latest as $row) {
            if ($row->quran_surah_id === null) {
                continue;
            }

            $max = (int) $row->surah?->ayah_count;
            if ($max < 1) {
                continue;
            }

            $map[(int) $row->santri_id][(int) $row->quran_surah_id] = $row->ayah_end < $max
                ? $row->ayah_end + 1
                : 1;
        }

        return $map;
    }

    private function resolvedSession(Request $request): ?AttendanceSession
    {
        if (! $request->filled('sesi')) {
            return null;
        }

        $session = AttendanceSession::query()
            ->with('schedule.halaqah')
            ->findOrFail($request->integer('sesi'));
        $this->access->assertHalaqah($request->user(), $session->schedule->halaqah);

        return $session;
    }

    /**
     * @return Collection<int, int>
     */
    private function hadirSantriIds(User $user, AttendanceSession $session): Collection
    {
        $hadirIds = $session->attendances()
            ->where('status', AttendanceStatus::Hadir)
            ->pluck('santri_id');

        $allowed = $this->access->guidedMemberQuery($user)->pluck('santri_id');

        return $hadirIds
            ->map(fn ($id): int => (int) $id)
            ->intersect($allowed->map(fn ($id): int => (int) $id))
            ->values();
    }
}
