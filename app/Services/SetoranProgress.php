<?php

namespace App\Services;

use App\Enums\SetoranStatus;
use App\Enums\SetoranSubtype;
use App\Models\HafalanSetoran;
use App\Support\QuranCatalog;

/**
 * Ringkasan posisi + % juz aktif untuk bacaan Alquran / hafalan Juz 30.
 */
final class ActiveJuzSnapshot
{
    public function __construct(
        public JuzProgress $juz,
        public string $positionLabel,
        public int $surahId,
        public string $surahName,
        public int $ayahStart,
        public int $ayahEnd,
    ) {}

    public function percentLabel(): string
    {
        return rtrim(rtrim(number_format($this->juz->percent, 2, '.', ''), '0'), '.').'%';
    }
}

/**
 * @phpstan-type BacaanRow array{
 *     alquran: ?ActiveJuzSnapshot,
 *     iqroLabel: ?string,
 *     summary: string,
 *     percent: float
 * }
 * @phpstan-type HafalanRow array{
 *     juz30: ?ActiveJuzSnapshot,
 *     doaName: ?string,
 *     doaStatus: ?SetoranStatus,
 *     summary: string,
 *     percent: float
 * }
 */
class SetoranProgress
{
    public function __construct(
        private HafalanProgress $juzProgress,
    ) {}

    /**
     * @param  list<int>  $santriIds
     * @return array<int, BacaanRow>
     */
    public function bacaanForMany(array $santriIds, int $academicYearId): array
    {
        $juzMap = $this->juzProgress->forMany($santriIds, $academicYearId, SetoranSubtype::Alquran);
        $lastAlquran = $this->latestBySubtype($santriIds, $academicYearId, SetoranSubtype::Alquran, onlyLulus: true);
        $lastIqro = $this->latestBySubtype($santriIds, $academicYearId, SetoranSubtype::Iqro, onlyLulus: false);

        $out = [];
        foreach ($santriIds as $id) {
            $alquran = $this->activeSnapshot($lastAlquran[$id] ?? null, $juzMap[$id] ?? null);
            $iqroLabel = $this->iqroLabel($lastIqro[$id] ?? null);
            $summary = $alquran?->positionLabel
                ?? $iqroLabel
                ?? 'Belum ada bacaan';
            $out[$id] = [
                'alquran' => $alquran,
                'iqroLabel' => $iqroLabel,
                'summary' => $summary,
                'percent' => $alquran?->juz->percent ?? 0.0,
            ];
        }

        return $out;
    }

    /**
     * @param  list<int>  $santriIds
     * @return array<int, HafalanRow>
     */
    public function hafalanForMany(array $santriIds, int $academicYearId): array
    {
        $juzMap = $this->juzProgress->forMany($santriIds, $academicYearId, SetoranSubtype::Juz30);
        $lastJuz30 = $this->latestBySubtype($santriIds, $academicYearId, SetoranSubtype::Juz30, onlyLulus: true);
        $lastDoa = $this->latestBySubtype($santriIds, $academicYearId, SetoranSubtype::Doa, onlyLulus: false);

        $out = [];
        foreach ($santriIds as $id) {
            $juz30 = $this->activeSnapshot($lastJuz30[$id] ?? null, $juzMap[$id] ?? null, forceJuz: 30);
            $doa = $lastDoa[$id] ?? null;
            $doaName = $doa?->doa_name;
            $doaStatus = $doa?->status;
            $summary = $juz30?->positionLabel
                ?? ($doaName ? 'Doa: '.$doaName : null)
                ?? 'Belum ada hafalan';
            $out[$id] = [
                'juz30' => $juz30,
                'doaName' => $doaName,
                'doaStatus' => $doaStatus,
                'summary' => $summary,
                'percent' => $juz30?->juz->percent ?? 0.0,
            ];
        }

        return $out;
    }

    /**
     * @return BacaanRow
     */
    public function bacaanForSantri(int $santriId, int $academicYearId): array
    {
        return $this->bacaanForMany([$santriId], $academicYearId)[$santriId];
    }

    /**
     * @return HafalanRow
     */
    public function hafalanForSantri(int $santriId, int $academicYearId): array
    {
        return $this->hafalanForMany([$santriId], $academicYearId)[$santriId];
    }

    private function activeSnapshot(
        ?HafalanSetoran $last,
        ?HafalanProgressResult $progress,
        ?int $forceJuz = null,
    ): ?ActiveJuzSnapshot {
        if (! $last || ! $progress || $last->quran_surah_id === null) {
            return null;
        }

        $juzNumber = $forceJuz
            ?? QuranCatalog::juzForAyah((int) $last->quran_surah_id, (int) ($last->ayah_end ?? $last->ayah_start ?? 1));

        if ($juzNumber === null) {
            return null;
        }

        $bar = $progress->juz($juzNumber);
        if (! $bar) {
            return null;
        }

        $surahName = $last->surah?->name_id ?? 'Surat';
        $ayahRange = $last->ayahRange();
        $position = $surahName.' ayat '.$ayahRange.' · Juz '.$juzNumber;

        return new ActiveJuzSnapshot(
            juz: $bar,
            positionLabel: $position,
            surahId: (int) $last->quran_surah_id,
            surahName: $surahName,
            ayahStart: (int) $last->ayah_start,
            ayahEnd: (int) $last->ayah_end,
        );
    }

    private function iqroLabel(?HafalanSetoran $row): ?string
    {
        if (! $row || $row->iqro_level === null) {
            return null;
        }

        return 'Iqro '.$row->iqro_level.' hlm. '.$row->iqro_page;
    }

    /**
     * @param  list<int>  $santriIds
     * @return array<int, HafalanSetoran>
     */
    private function latestBySubtype(
        array $santriIds,
        int $academicYearId,
        SetoranSubtype $subtype,
        bool $onlyLulus,
    ): array {
        if ($santriIds === []) {
            return [];
        }

        $query = HafalanSetoran::query()
            ->with('surah:id,name_id,ayah_count')
            ->whereIn('santri_id', $santriIds)
            ->where('academic_year_id', $academicYearId)
            ->where('subtype', $subtype)
            ->orderByDesc('setoran_date')
            ->orderByDesc('id');

        if ($onlyLulus) {
            $query->where('status', SetoranStatus::Lulus);
        }

        $latest = [];
        foreach ($query->get() as $row) {
            $id = (int) $row->santri_id;
            if (! isset($latest[$id])) {
                $latest[$id] = $row;
            }
        }

        return $latest;
    }
}
