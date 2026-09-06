<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quran_juz', function (Blueprint $table) {
            $table->unsignedTinyInteger('number')->primary();
            $table->unsignedTinyInteger('start_surah_id');
            $table->unsignedSmallInteger('start_ayah');
            $table->unsignedTinyInteger('end_surah_id');
            $table->unsignedSmallInteger('end_ayah');

            $table->foreign('start_surah_id')->references('id')->on('quran_surahs');
            $table->foreign('end_surah_id')->references('id')->on('quran_surahs');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quran_juz');
    }
};
