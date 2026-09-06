<?php

namespace Database\Seeders;

use App\Models\QuranJuz;
use App\Models\QuranSurah;
use Illuminate\Database\Seeder;

class QuranSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->surahs() as $surah) {
            QuranSurah::query()->updateOrCreate(
                ['id' => $surah['id']],
                [
                    'name_id' => $surah['name_id'],
                    'name_ar' => $surah['name_ar'],
                    'ayah_count' => $surah['ayah_count'],
                ],
            );
        }

        foreach ($this->juz() as $juz) {
            QuranJuz::query()->updateOrCreate(
                ['number' => $juz['number']],
                [
                    'start_surah_id' => $juz['start_surah_id'],
                    'start_ayah' => $juz['start_ayah'],
                    'end_surah_id' => $juz['end_surah_id'],
                    'end_ayah' => $juz['end_ayah'],
                ],
            );
        }
    }

    /**
     * @return list<array{id: int, name_id: string, name_ar: string, ayah_count: int}>
     */
    private function surahs(): array
    {
        return [
            ['id' => 1, 'name_id' => 'Al-Fatihah', 'name_ar' => 'الفاتحة', 'ayah_count' => 7],
            ['id' => 2, 'name_id' => 'Al-Baqarah', 'name_ar' => 'البقرة', 'ayah_count' => 286],
            ['id' => 3, 'name_id' => 'Ali Imran', 'name_ar' => 'آل عمران', 'ayah_count' => 200],
            ['id' => 4, 'name_id' => 'An-Nisa', 'name_ar' => 'النساء', 'ayah_count' => 176],
            ['id' => 5, 'name_id' => 'Al-Maidah', 'name_ar' => 'المائدة', 'ayah_count' => 120],
            ['id' => 6, 'name_id' => 'Al-Anam', 'name_ar' => 'الأنعام', 'ayah_count' => 165],
            ['id' => 7, 'name_id' => 'Al-Araf', 'name_ar' => 'الأعراف', 'ayah_count' => 206],
            ['id' => 8, 'name_id' => 'Al-Anfal', 'name_ar' => 'الأنفال', 'ayah_count' => 75],
            ['id' => 9, 'name_id' => 'At-Taubah', 'name_ar' => 'التوبة', 'ayah_count' => 129],
            ['id' => 10, 'name_id' => 'Yunus', 'name_ar' => 'يونس', 'ayah_count' => 109],
            ['id' => 11, 'name_id' => 'Hud', 'name_ar' => 'هود', 'ayah_count' => 123],
            ['id' => 12, 'name_id' => 'Yusuf', 'name_ar' => 'يوسف', 'ayah_count' => 111],
            ['id' => 13, 'name_id' => 'Ar-Rad', 'name_ar' => 'الرعد', 'ayah_count' => 43],
            ['id' => 14, 'name_id' => 'Ibrahim', 'name_ar' => 'إبراهيم', 'ayah_count' => 52],
            ['id' => 15, 'name_id' => 'Al-Hijr', 'name_ar' => 'الحجر', 'ayah_count' => 99],
            ['id' => 16, 'name_id' => 'An-Nahl', 'name_ar' => 'النحل', 'ayah_count' => 128],
            ['id' => 17, 'name_id' => 'Al-Isra', 'name_ar' => 'الإسراء', 'ayah_count' => 111],
            ['id' => 18, 'name_id' => 'Al-Kahf', 'name_ar' => 'الكهف', 'ayah_count' => 110],
            ['id' => 19, 'name_id' => 'Maryam', 'name_ar' => 'مريم', 'ayah_count' => 98],
            ['id' => 20, 'name_id' => 'Taha', 'name_ar' => 'طه', 'ayah_count' => 135],
            ['id' => 21, 'name_id' => 'Al-Anbiya', 'name_ar' => 'الأنبياء', 'ayah_count' => 112],
            ['id' => 22, 'name_id' => 'Al-Hajj', 'name_ar' => 'الحج', 'ayah_count' => 78],
            ['id' => 23, 'name_id' => 'Al-Muminun', 'name_ar' => 'المؤمنون', 'ayah_count' => 118],
            ['id' => 24, 'name_id' => 'An-Nur', 'name_ar' => 'النور', 'ayah_count' => 64],
            ['id' => 25, 'name_id' => 'Al-Furqan', 'name_ar' => 'الفرقان', 'ayah_count' => 77],
            ['id' => 26, 'name_id' => 'Asy-Syuara', 'name_ar' => 'الشعراء', 'ayah_count' => 227],
            ['id' => 27, 'name_id' => 'An-Naml', 'name_ar' => 'النمل', 'ayah_count' => 93],
            ['id' => 28, 'name_id' => 'Al-Qasas', 'name_ar' => 'القصص', 'ayah_count' => 88],
            ['id' => 29, 'name_id' => 'Al-Ankabut', 'name_ar' => 'العنكبوت', 'ayah_count' => 69],
            ['id' => 30, 'name_id' => 'Ar-Rum', 'name_ar' => 'الروم', 'ayah_count' => 60],
            ['id' => 31, 'name_id' => 'Luqman', 'name_ar' => 'لقمان', 'ayah_count' => 34],
            ['id' => 32, 'name_id' => 'As-Sajdah', 'name_ar' => 'السجدة', 'ayah_count' => 30],
            ['id' => 33, 'name_id' => 'Al-Ahzab', 'name_ar' => 'الأحزاب', 'ayah_count' => 73],
            ['id' => 34, 'name_id' => 'Saba', 'name_ar' => 'سبأ', 'ayah_count' => 54],
            ['id' => 35, 'name_id' => 'Fatir', 'name_ar' => 'فاطر', 'ayah_count' => 45],
            ['id' => 36, 'name_id' => 'Yasin', 'name_ar' => 'يس', 'ayah_count' => 83],
            ['id' => 37, 'name_id' => 'As-Saffat', 'name_ar' => 'الصافات', 'ayah_count' => 182],
            ['id' => 38, 'name_id' => 'Sad', 'name_ar' => 'ص', 'ayah_count' => 88],
            ['id' => 39, 'name_id' => 'Az-Zumar', 'name_ar' => 'الزمر', 'ayah_count' => 75],
            ['id' => 40, 'name_id' => 'Ghafir', 'name_ar' => 'غافر', 'ayah_count' => 85],
            ['id' => 41, 'name_id' => 'Fussilat', 'name_ar' => 'فصلت', 'ayah_count' => 54],
            ['id' => 42, 'name_id' => 'Asy-Syura', 'name_ar' => 'الشورى', 'ayah_count' => 53],
            ['id' => 43, 'name_id' => 'Az-Zukhruf', 'name_ar' => 'الزخرف', 'ayah_count' => 89],
            ['id' => 44, 'name_id' => 'Ad-Dukhan', 'name_ar' => 'الدخان', 'ayah_count' => 59],
            ['id' => 45, 'name_id' => 'Al-Jasiyah', 'name_ar' => 'الجاثية', 'ayah_count' => 37],
            ['id' => 46, 'name_id' => 'Al-Ahqaf', 'name_ar' => 'الأحقاف', 'ayah_count' => 35],
            ['id' => 47, 'name_id' => 'Muhammad', 'name_ar' => 'محمد', 'ayah_count' => 38],
            ['id' => 48, 'name_id' => 'Al-Fath', 'name_ar' => 'الفتح', 'ayah_count' => 29],
            ['id' => 49, 'name_id' => 'Al-Hujurat', 'name_ar' => 'الحجرات', 'ayah_count' => 18],
            ['id' => 50, 'name_id' => 'Qaf', 'name_ar' => 'ق', 'ayah_count' => 45],
            ['id' => 51, 'name_id' => 'Az-Zariyat', 'name_ar' => 'الذاريات', 'ayah_count' => 60],
            ['id' => 52, 'name_id' => 'At-Tur', 'name_ar' => 'الطور', 'ayah_count' => 49],
            ['id' => 53, 'name_id' => 'An-Najm', 'name_ar' => 'النجم', 'ayah_count' => 62],
            ['id' => 54, 'name_id' => 'Al-Qamar', 'name_ar' => 'القمر', 'ayah_count' => 55],
            ['id' => 55, 'name_id' => 'Ar-Rahman', 'name_ar' => 'الرحمن', 'ayah_count' => 78],
            ['id' => 56, 'name_id' => 'Al-Waqiah', 'name_ar' => 'الواقعة', 'ayah_count' => 96],
            ['id' => 57, 'name_id' => 'Al-Hadid', 'name_ar' => 'الحديد', 'ayah_count' => 29],
            ['id' => 58, 'name_id' => 'Al-Mujadilah', 'name_ar' => 'المجادلة', 'ayah_count' => 22],
            ['id' => 59, 'name_id' => 'Al-Hasyr', 'name_ar' => 'الحشر', 'ayah_count' => 24],
            ['id' => 60, 'name_id' => 'Al-Mumtahanah', 'name_ar' => 'الممتحنة', 'ayah_count' => 13],
            ['id' => 61, 'name_id' => 'As-Saff', 'name_ar' => 'الصف', 'ayah_count' => 14],
            ['id' => 62, 'name_id' => 'Al-Jumuah', 'name_ar' => 'الجمعة', 'ayah_count' => 11],
            ['id' => 63, 'name_id' => 'Al-Munafiqun', 'name_ar' => 'المنافقون', 'ayah_count' => 11],
            ['id' => 64, 'name_id' => 'At-Tagabun', 'name_ar' => 'التغابن', 'ayah_count' => 18],
            ['id' => 65, 'name_id' => 'At-Talaq', 'name_ar' => 'الطلاق', 'ayah_count' => 12],
            ['id' => 66, 'name_id' => 'At-Tahrim', 'name_ar' => 'التحريم', 'ayah_count' => 12],
            ['id' => 67, 'name_id' => 'Al-Mulk', 'name_ar' => 'الملك', 'ayah_count' => 30],
            ['id' => 68, 'name_id' => 'Al-Qalam', 'name_ar' => 'القلم', 'ayah_count' => 52],
            ['id' => 69, 'name_id' => 'Al-Haqqah', 'name_ar' => 'الحاقة', 'ayah_count' => 52],
            ['id' => 70, 'name_id' => 'Al-Maarij', 'name_ar' => 'المعارج', 'ayah_count' => 44],
            ['id' => 71, 'name_id' => 'Nuh', 'name_ar' => 'نوح', 'ayah_count' => 28],
            ['id' => 72, 'name_id' => 'Al-Jinn', 'name_ar' => 'الجن', 'ayah_count' => 28],
            ['id' => 73, 'name_id' => 'Al-Muzzammil', 'name_ar' => 'المزمل', 'ayah_count' => 20],
            ['id' => 74, 'name_id' => 'Al-Muddassir', 'name_ar' => 'المدثر', 'ayah_count' => 56],
            ['id' => 75, 'name_id' => 'Al-Qiyamah', 'name_ar' => 'القيامة', 'ayah_count' => 40],
            ['id' => 76, 'name_id' => 'Al-Insan', 'name_ar' => 'الإنسان', 'ayah_count' => 31],
            ['id' => 77, 'name_id' => 'Al-Mursalat', 'name_ar' => 'المرسلات', 'ayah_count' => 50],
            ['id' => 78, 'name_id' => 'An-Naba', 'name_ar' => 'النبأ', 'ayah_count' => 40],
            ['id' => 79, 'name_id' => 'An-Naziat', 'name_ar' => 'النازعات', 'ayah_count' => 46],
            ['id' => 80, 'name_id' => 'Abasa', 'name_ar' => 'عبس', 'ayah_count' => 42],
            ['id' => 81, 'name_id' => 'At-Takwir', 'name_ar' => 'التكوير', 'ayah_count' => 29],
            ['id' => 82, 'name_id' => 'Al-Infitar', 'name_ar' => 'الإنفطار', 'ayah_count' => 19],
            ['id' => 83, 'name_id' => 'Al-Mutaffifin', 'name_ar' => 'المطففين', 'ayah_count' => 36],
            ['id' => 84, 'name_id' => 'Al-Insyiqaq', 'name_ar' => 'الإنشقاق', 'ayah_count' => 25],
            ['id' => 85, 'name_id' => 'Al-Buruj', 'name_ar' => 'البروج', 'ayah_count' => 22],
            ['id' => 86, 'name_id' => 'At-Tariq', 'name_ar' => 'الطارق', 'ayah_count' => 17],
            ['id' => 87, 'name_id' => 'Al-Ala', 'name_ar' => 'الأعلى', 'ayah_count' => 19],
            ['id' => 88, 'name_id' => 'Al-Gasyiyah', 'name_ar' => 'الغاشية', 'ayah_count' => 26],
            ['id' => 89, 'name_id' => 'Al-Fajr', 'name_ar' => 'الفجر', 'ayah_count' => 30],
            ['id' => 90, 'name_id' => 'Al-Balad', 'name_ar' => 'البلد', 'ayah_count' => 20],
            ['id' => 91, 'name_id' => 'Asy-Syams', 'name_ar' => 'الشمس', 'ayah_count' => 15],
            ['id' => 92, 'name_id' => 'Al-Lail', 'name_ar' => 'الليل', 'ayah_count' => 21],
            ['id' => 93, 'name_id' => 'Ad-Duha', 'name_ar' => 'الضحى', 'ayah_count' => 11],
            ['id' => 94, 'name_id' => 'Asy-Syarh', 'name_ar' => 'الشرح', 'ayah_count' => 8],
            ['id' => 95, 'name_id' => 'At-Tin', 'name_ar' => 'التين', 'ayah_count' => 8],
            ['id' => 96, 'name_id' => 'Al-Alaq', 'name_ar' => 'العلق', 'ayah_count' => 19],
            ['id' => 97, 'name_id' => 'Al-Qadr', 'name_ar' => 'القدر', 'ayah_count' => 5],
            ['id' => 98, 'name_id' => 'Al-Bayyinah', 'name_ar' => 'البينة', 'ayah_count' => 8],
            ['id' => 99, 'name_id' => 'Az-Zalzalah', 'name_ar' => 'الزلزلة', 'ayah_count' => 8],
            ['id' => 100, 'name_id' => 'Al-Adiyat', 'name_ar' => 'العاديات', 'ayah_count' => 11],
            ['id' => 101, 'name_id' => 'Al-Qariah', 'name_ar' => 'القارعة', 'ayah_count' => 11],
            ['id' => 102, 'name_id' => 'At-Takasur', 'name_ar' => 'التكاثر', 'ayah_count' => 8],
            ['id' => 103, 'name_id' => 'Al-Asr', 'name_ar' => 'العصر', 'ayah_count' => 3],
            ['id' => 104, 'name_id' => 'Al-Humazah', 'name_ar' => 'الهمزة', 'ayah_count' => 9],
            ['id' => 105, 'name_id' => 'Al-Fil', 'name_ar' => 'الفيل', 'ayah_count' => 5],
            ['id' => 106, 'name_id' => 'Quraisy', 'name_ar' => 'قريش', 'ayah_count' => 4],
            ['id' => 107, 'name_id' => 'Al-Maun', 'name_ar' => 'الماعون', 'ayah_count' => 7],
            ['id' => 108, 'name_id' => 'Al-Kausar', 'name_ar' => 'الكوثر', 'ayah_count' => 3],
            ['id' => 109, 'name_id' => 'Al-Kafirun', 'name_ar' => 'الكافرون', 'ayah_count' => 6],
            ['id' => 110, 'name_id' => 'An-Nasr', 'name_ar' => 'النصر', 'ayah_count' => 3],
            ['id' => 111, 'name_id' => 'Al-Lahab', 'name_ar' => 'اللهب', 'ayah_count' => 5],
            ['id' => 112, 'name_id' => 'Al-Ikhlas', 'name_ar' => 'الإخلاص', 'ayah_count' => 4],
            ['id' => 113, 'name_id' => 'Al-Falaq', 'name_ar' => 'الفلق', 'ayah_count' => 5],
            ['id' => 114, 'name_id' => 'An-Nas', 'name_ar' => 'الناس', 'ayah_count' => 6],
        ];
    }

    /**
     * Batas juz mushaf Madinah / Utsmani.
     *
     * @return list<array{number: int, start_surah_id: int, start_ayah: int, end_surah_id: int, end_ayah: int}>
     */
    private function juz(): array
    {
        return [
            ['number' => 1, 'start_surah_id' => 1, 'start_ayah' => 1, 'end_surah_id' => 2, 'end_ayah' => 141],
            ['number' => 2, 'start_surah_id' => 2, 'start_ayah' => 142, 'end_surah_id' => 2, 'end_ayah' => 252],
            ['number' => 3, 'start_surah_id' => 2, 'start_ayah' => 253, 'end_surah_id' => 3, 'end_ayah' => 92],
            ['number' => 4, 'start_surah_id' => 3, 'start_ayah' => 93, 'end_surah_id' => 4, 'end_ayah' => 23],
            ['number' => 5, 'start_surah_id' => 4, 'start_ayah' => 24, 'end_surah_id' => 4, 'end_ayah' => 147],
            ['number' => 6, 'start_surah_id' => 4, 'start_ayah' => 148, 'end_surah_id' => 5, 'end_ayah' => 81],
            ['number' => 7, 'start_surah_id' => 5, 'start_ayah' => 82, 'end_surah_id' => 6, 'end_ayah' => 110],
            ['number' => 8, 'start_surah_id' => 6, 'start_ayah' => 111, 'end_surah_id' => 7, 'end_ayah' => 87],
            ['number' => 9, 'start_surah_id' => 7, 'start_ayah' => 88, 'end_surah_id' => 8, 'end_ayah' => 40],
            ['number' => 10, 'start_surah_id' => 8, 'start_ayah' => 41, 'end_surah_id' => 9, 'end_ayah' => 92],
            ['number' => 11, 'start_surah_id' => 9, 'start_ayah' => 93, 'end_surah_id' => 11, 'end_ayah' => 5],
            ['number' => 12, 'start_surah_id' => 11, 'start_ayah' => 6, 'end_surah_id' => 12, 'end_ayah' => 52],
            ['number' => 13, 'start_surah_id' => 12, 'start_ayah' => 53, 'end_surah_id' => 14, 'end_ayah' => 52],
            ['number' => 14, 'start_surah_id' => 15, 'start_ayah' => 1, 'end_surah_id' => 16, 'end_ayah' => 128],
            ['number' => 15, 'start_surah_id' => 17, 'start_ayah' => 1, 'end_surah_id' => 18, 'end_ayah' => 74],
            ['number' => 16, 'start_surah_id' => 18, 'start_ayah' => 75, 'end_surah_id' => 20, 'end_ayah' => 135],
            ['number' => 17, 'start_surah_id' => 21, 'start_ayah' => 1, 'end_surah_id' => 22, 'end_ayah' => 78],
            ['number' => 18, 'start_surah_id' => 23, 'start_ayah' => 1, 'end_surah_id' => 25, 'end_ayah' => 20],
            ['number' => 19, 'start_surah_id' => 25, 'start_ayah' => 21, 'end_surah_id' => 27, 'end_ayah' => 55],
            ['number' => 20, 'start_surah_id' => 27, 'start_ayah' => 56, 'end_surah_id' => 29, 'end_ayah' => 45],
            ['number' => 21, 'start_surah_id' => 29, 'start_ayah' => 46, 'end_surah_id' => 33, 'end_ayah' => 30],
            ['number' => 22, 'start_surah_id' => 33, 'start_ayah' => 31, 'end_surah_id' => 36, 'end_ayah' => 27],
            ['number' => 23, 'start_surah_id' => 36, 'start_ayah' => 28, 'end_surah_id' => 39, 'end_ayah' => 31],
            ['number' => 24, 'start_surah_id' => 39, 'start_ayah' => 32, 'end_surah_id' => 41, 'end_ayah' => 46],
            ['number' => 25, 'start_surah_id' => 41, 'start_ayah' => 47, 'end_surah_id' => 45, 'end_ayah' => 37],
            ['number' => 26, 'start_surah_id' => 46, 'start_ayah' => 1, 'end_surah_id' => 51, 'end_ayah' => 30],
            ['number' => 27, 'start_surah_id' => 51, 'start_ayah' => 31, 'end_surah_id' => 57, 'end_ayah' => 29],
            ['number' => 28, 'start_surah_id' => 58, 'start_ayah' => 1, 'end_surah_id' => 66, 'end_ayah' => 12],
            ['number' => 29, 'start_surah_id' => 67, 'start_ayah' => 1, 'end_surah_id' => 77, 'end_ayah' => 50],
            ['number' => 30, 'start_surah_id' => 78, 'start_ayah' => 1, 'end_surah_id' => 114, 'end_ayah' => 6],
        ];
    }
}
