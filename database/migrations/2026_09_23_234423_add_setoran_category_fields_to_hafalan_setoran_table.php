<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hafalan_setoran', function (Blueprint $table) {
            $table->string('category', 20)->nullable()->after('activity_type');
            $table->string('subtype', 20)->nullable()->after('category');
            $table->string('doa_name')->nullable()->after('iqro_page');
        });

        DB::table('hafalan_setoran')->orderBy('id')->chunkById(200, function ($rows): void {
            foreach ($rows as $row) {
                if ($row->iqro_level !== null) {
                    $category = 'bacaan';
                    $subtype = 'iqro';
                } elseif ($row->activity_type === 'hafalan') {
                    $category = 'hafalan';
                    $subtype = 'juz30';
                } else {
                    $category = 'bacaan';
                    $subtype = 'alquran';
                }

                DB::table('hafalan_setoran')->where('id', $row->id)->update([
                    'category' => $category,
                    'subtype' => $subtype,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('hafalan_setoran', function (Blueprint $table) {
            $table->dropColumn(['category', 'subtype', 'doa_name']);
        });
    }
};
