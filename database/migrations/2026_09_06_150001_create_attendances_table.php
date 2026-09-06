<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santri_profiles')->restrictOnDelete();
            $table->string('status', 20);
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['attendance_session_id', 'santri_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
