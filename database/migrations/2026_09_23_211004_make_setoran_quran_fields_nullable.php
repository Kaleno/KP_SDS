<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hafalan_setoran') || Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        // Live DBs may still have NOT NULL from an older schema; Iqro setoran needs nulls.
        DB::statement('ALTER TABLE hafalan_setoran MODIFY quran_surah_id TINYINT UNSIGNED NULL');
        DB::statement('ALTER TABLE hafalan_setoran MODIFY ayah_start SMALLINT UNSIGNED NULL');
        DB::statement('ALTER TABLE hafalan_setoran MODIFY ayah_end SMALLINT UNSIGNED NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('hafalan_setoran') || Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE hafalan_setoran MODIFY quran_surah_id TINYINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE hafalan_setoran MODIFY ayah_start SMALLINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE hafalan_setoran MODIFY ayah_end SMALLINT UNSIGNED NOT NULL');
    }
};
