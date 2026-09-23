<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Flatten ops: one attendance session per day, no kelas / jadwal per kelas / tahun ajaran.
 * Wipes operational class-scoped data so the new model stays audit-clean.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('attendances')) {
            DB::table('attendances')->delete();
        }
        if (Schema::hasTable('attendance_sessions')) {
            DB::table('attendance_sessions')->delete();
        }
        if (Schema::hasTable('hafalan_setoran')) {
            DB::table('hafalan_setoran')->delete();
        }
        if (Schema::hasTable('schedules')) {
            DB::table('schedules')->delete();
        }
        if (Schema::hasTable('halaqah_members')) {
            DB::table('halaqah_members')->delete();
        }
        if (Schema::hasTable('halaqah')) {
            DB::table('halaqah')->delete();
        }
        if (Schema::hasTable('academic_years')) {
            DB::table('academic_years')->delete();
        }

        if (Schema::hasColumn('attendance_sessions', 'schedule_id')) {
            Schema::table('attendance_sessions', function (Blueprint $table): void {
                $table->dropForeign(['schedule_id']);
            });

            Schema::table('attendance_sessions', function (Blueprint $table): void {
                $table->dropUnique(['schedule_id', 'session_date']);
                $table->dropColumn('schedule_id');
                $table->unique('session_date');
            });
        }

        if (Schema::hasColumn('hafalan_setoran', 'halaqah_id')) {
            Schema::table('hafalan_setoran', function (Blueprint $table): void {
                $table->dropForeign(['halaqah_id']);
                $table->dropForeign(['academic_year_id']);
            });

            Schema::table('hafalan_setoran', function (Blueprint $table): void {
                $table->dropIndex(['santri_id', 'academic_year_id', 'status']);
                $table->dropColumn(['halaqah_id', 'academic_year_id']);
                $table->index(['santri_id', 'status']);
            });
        }

        Schema::dropIfExists('schedules');
        Schema::dropIfExists('halaqah_members');
        Schema::dropIfExists('halaqah');
        Schema::dropIfExists('academic_years');
    }

    public function down(): void
    {
        // Irreversible flatten — restore from backup / prior migrations if needed.
    }
};
