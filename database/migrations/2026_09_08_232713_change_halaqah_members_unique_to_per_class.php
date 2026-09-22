<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MySQL keeps the `santri_id` foreign key on the leftmost column of
     * `halaqah_members_active_unique`, so that column needs its own index
     * before the composite unique can be dropped.
     */
    public function up(): void
    {
        Schema::table('halaqah_members', function (Blueprint $table) {
            $table->index('santri_id', 'halaqah_members_santri_id_index');
            $table->dropUnique('halaqah_members_active_unique');
            $table->unique(['halaqah_id', 'santri_id', 'active_slot'], 'halaqah_members_class_active_unique');
        });
    }

    public function down(): void
    {
        Schema::table('halaqah_members', function (Blueprint $table) {
            $table->unique(['santri_id', 'academic_year_id', 'active_slot'], 'halaqah_members_active_unique');
            $table->dropUnique('halaqah_members_class_active_unique');
            $table->dropIndex('halaqah_members_santri_id_index');
        });
    }
};
