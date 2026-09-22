<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hafalan_setoran') && ! Schema::hasColumn('hafalan_setoran', 'activity_type')) {
            Schema::table('hafalan_setoran', function (Blueprint $table) {
                $table->string('activity_type', 20)->default('ngaji')->after('academic_year_id');
                $table->unsignedTinyInteger('iqro_level')->nullable()->after('activity_type');
                $table->unsignedSmallInteger('iqro_page')->nullable()->after('iqro_level');
            });
        }

        if (Schema::hasTable('hafalan_setoran')) {
            DB::table('hafalan_setoran')->where('status', 'lancar')->update(['status' => 'lulus']);
            DB::table('hafalan_setoran')->whereIn('status', ['ulang', 'perbaikan'])->update(['status' => 'mengulang']);
        }

        if (! Schema::hasTable('holidays')) {
            Schema::create('holidays', function (Blueprint $table) {
                $table->id();
                $table->date('date')->unique();
                $table->string('name');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');

        if (Schema::hasTable('hafalan_setoran')) {
            DB::table('hafalan_setoran')->where('status', 'lulus')->update(['status' => 'lancar']);
            DB::table('hafalan_setoran')->where('status', 'mengulang')->update(['status' => 'ulang']);

            if (Schema::hasColumn('hafalan_setoran', 'activity_type')) {
                Schema::table('hafalan_setoran', function (Blueprint $table) {
                    $table->dropColumn(['activity_type', 'iqro_level', 'iqro_page']);
                });
            }
        }
    }
};
