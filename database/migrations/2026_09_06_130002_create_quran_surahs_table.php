<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quran_surahs', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('name_id');
            $table->string('name_ar')->nullable();
            $table->unsignedSmallInteger('ayah_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quran_surahs');
    }
};
