<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('halaqah_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('halaqah_id')->constrained('halaqah')->restrictOnDelete();
            $table->foreignId('santri_id')->constrained('santri_profiles')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->date('started_at');
            $table->date('ended_at')->nullable();
            $table->string('mutation_note')->nullable();
            $table->unsignedTinyInteger('active_slot')->nullable();
            $table->timestamps();

            $table->unique(['santri_id', 'academic_year_id', 'active_slot'], 'halaqah_members_active_unique');
            $table->index(['halaqah_id', 'ended_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('halaqah_members');
    }
};
