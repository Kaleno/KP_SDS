<?php

namespace App\Support;

use App\Models\QuranJuz;
use App\Models\QuranSurah;
use Illuminate\Support\Collection;

class QuranCatalog
{
    /**
     * @return array{start: int, end: int, label: string}|null
     */
    public static function juzMetaForSurah(int $surahId): ?array
    {
        return self::surahJuzMeta()[$surahId] ?? null;
    }

    public static function juzForAyah(int $surahId, int $ayah): ?int
    {
        return self::ayahToJuzMap()[$surahId.':'.$ayah] ?? null;
    }

    /**
     * @return array<string, int>
     */
    public static function ayahToJuzMap(): array
    {
        return once(function (): array {
            $ayahCounts = QuranSurah::query()->pluck('ayah_count', 'id');
            $map = [];

            foreach (QuranJuz::query()->orderBy('number')->get() as $juz) {
                for ($surahId = (int) $juz->start_surah_id; $surahId <= (int) $juz->end_surah_id; $surahId++) {
                    $ayahStart = $surahId === (int) $juz->start_surah_id ? (int) $juz->start_ayah : 1;
                    $ayahEnd = $surahId === (int) $juz->end_surah_id
                        ? (int) $juz->end_ayah
                        : (int) ($ayahCounts[$surahId] ?? 0);

                    for ($ayah = $ayahStart; $ayah <= $ayahEnd; $ayah++) {
                        $map[$surahId.':'.$ayah] = (int) $juz->number;
                    }
                }
            }

            return $map;
        });
    }

    /**
     * @return Collection<int, array{id: int, name: string, ayah: int, juz: int|null, juz_end: int|null, juz_label: string, label: string}>
     */
    public static function surahPickerItems(Collection $surahs, bool $withJuz = true): Collection
    {
        $meta = self::surahJuzMeta();

        return $surahs->map(function (QuranSurah $surah) use ($withJuz, $meta): array {
            $juzMeta = $meta[(int) $surah->id] ?? null;
            $juzLabel = $juzMeta['label'] ?? '';
            $base = $surah->id.'. '.$surah->name_id;

            return [
                'id' => (int) $surah->id,
                'name' => $surah->name_id,
                'ayah' => (int) $surah->ayah_count,
                'juz' => $juzMeta['start'] ?? null,
                'juz_end' => $juzMeta['end'] ?? null,
                'juz_label' => $juzLabel,
                'label' => $withJuz && $juzLabel !== ''
                    ? $base.' · '.$juzLabel.' ('.$surah->ayah_count.' ayat)'
                    : $base.' ('.$surah->ayah_count.' ayat)',
            ];
        })->values();
    }

    /**
     * @return array<int, array{start: int, end: int, label: string}>
     */
    private static function surahJuzMeta(): array
    {
        return once(function (): array {
            /** @var array<int, list<int>> $bySurah */
            $bySurah = [];
            foreach (QuranJuz::query()->orderBy('number')->get() as $juz) {
                for ($surahId = (int) $juz->start_surah_id; $surahId <= (int) $juz->end_surah_id; $surahId++) {
                    $bySurah[$surahId][] = (int) $juz->number;
                }
            }

            $meta = [];
            foreach ($bySurah as $surahId => $juzNumbers) {
                $juzNumbers = array_values(array_unique($juzNumbers));
                sort($juzNumbers);
                $start = $juzNumbers[0];
                $end = $juzNumbers[array_key_last($juzNumbers)];
                $meta[$surahId] = [
                    'start' => $start,
                    'end' => $end,
                    'label' => $start === $end ? 'Juz '.$start : 'Juz '.$start.'–'.$end,
                ];
            }

            return $meta;
        });
    }
}
