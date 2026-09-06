<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hafalan_setoran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri_profiles')->restrictOnDelete();
            $table->foreignId('halaqah_id')->constrained('halaqah')->restrictOnDelete();
            $table->foreignId('ustaz_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('quran_surah_id');
            $table->date('setoran_date');
            $table->unsignedSmallInteger('ayah_start');
            $table->unsignedSmallInteger('ayah_end');
            $table->string('status', 20);
            $table->text('note')->nullable();
            $table->string('correction_note')->nullable();
            $table->timestamps();

            $table->foreign('quran_surah_id')->references('id')->on('quran_surahs');
            $table->index(['santri_id', 'academic_year_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hafalan_setoran');
    }
};
