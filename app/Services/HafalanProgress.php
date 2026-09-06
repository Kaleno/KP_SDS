<?php

namespace App\Services;

use App\Enums\SetoranStatus;
use App\Models\AcademicYear;
use App\Models\HafalanSetoran;
use App\Models\QuranJuz;
use App\Models\QuranSurah;
use App\Models\SantriProfile;

class HafalanProgress
{
    /** @var array<string, int>|null ayah key "surah:ayah" => juz number */
    private ?array $ayahToJuz = null;

    /** @var array<int, int>|null juz number => ayah count */
    private ?array $juzTotals = null;

    public function forSantri(SantriProfile $santri, AcademicYear $year): HafalanProgressResult
    {
        return $this->forMany([$santri->id], $year)[$santri->id];
    }

    /**
     * @param  iterable<int>  $santriIds
     * @return array<int, HafalanProgressResult>
     */
    public function forMany(iterable $santriIds, AcademicYear $year): array
    {
        $ids = array_values(array_unique(array_map(
            intval(...),
            is_array($santriIds) ? $santriIds : iterator_to_array($santriIds),
        )));

        $this->ensureMap();

        $uniqueBySantri = [];
        foreach ($ids as $id) {
            $uniqueBySantri[$id] = [];
        }

        if ($ids === []) {
            return [];
        }

        $rows = HafalanSetoran::query()
            ->whereIn('santri_id', $ids)
            ->where('academic_year_id', $year->id)
            ->where('status', SetoranStatus::Lancar)
            ->get(['santri_id', 'quran_surah_id', 'ayah_start', 'ayah_end']);

        foreach ($rows as $row) {
            for ($ayah = $row->ayah_start; $ayah <= $row->ayah_end; $ayah++) {
                $uniqueBySantri[(int) $row->santri_id][$row->quran_surah_id.':'.$ayah] = true;
            }
        }

        $results = [];
        foreach ($ids as $id) {
            $results[$id] = $this->buildResult($uniqueBySantri[$id]);
        }

        return $results;
    }

    /**
     * @param  array<string, true>  $uniqueKeys
     */
    private function buildResult(array $uniqueKeys): HafalanProgressResult
    {
        $unique = 0;
        $juzCounts = array_fill(1, 30, 0);

        foreach ($uniqueKeys as $key => $_) {
            $juz = $this->ayahToJuz[$key] ?? null;
            if ($juz !== null) {
                $juzCounts[$juz]++;
                $unique++;
            }
        }

        $bars = [];
        for ($number = 1; $number <= 30; $number++) {
            $total = $this->juzTotals[$number] ?? 0;
            $lancar = $juzCounts[$number];
            $bars[] = new JuzProgress(
                number: $number,
                lancarCount: $lancar,
                ayahTotal: $total,
                percent: $total > 0 ? round($lancar / $total * 100, 2) : 0.0,
            );
        }

        $quranTotal = array_sum($this->juzTotals ?? []) ?: HafalanProgressResult::QURAN_AYAH_TOTAL;

        return new HafalanProgressResult(
            uniqueAyahCount: $unique,
            quranAyahTotal: $quranTotal,
            totalPercent: round($unique / $quranTotal * 100, 2),
            juz: $bars,
        );
    }

    private function ensureMap(): void
    {
        if ($this->ayahToJuz !== null) {
            return;
        }

        $surahs = QuranSurah::query()->get()->keyBy('id');
        $juzList = QuranJuz::query()->orderBy('number')->get();

        $ayahToJuz = [];
        $juzTotals = [];

        foreach ($juzList as $juz) {
            $count = 0;
            for ($surahId = $juz->start_surah_id; $surahId <= $juz->end_surah_id; $surahId++) {
                $ayahStart = $surahId === $juz->start_surah_id ? $juz->start_ayah : 1;
                $ayahEnd = $surahId === $juz->end_surah_id
                    ? $juz->end_ayah
                    : (int) $surahs[$surahId]->ayah_count;

                for ($ayah = $ayahStart; $ayah <= $ayahEnd; $ayah++) {
                    $ayahToJuz[$surahId.':'.$ayah] = (int) $juz->number;
                    $count++;
                }
            }
            $juzTotals[(int) $juz->number] = $count;
        }

        $this->ayahToJuz = $ayahToJuz;
        $this->juzTotals = $juzTotals;
    }
}
