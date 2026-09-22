<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('santri_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('parent_name');
            $table->string('school_level', 20);
            $table->date('birth_date');
            $table->string('gender', 20);
            $table->string('track', 20);
            $table->string('photo_path')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('rejection_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('santri_id')->nullable()->constrained('santri_profiles')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::table('santri_profiles', function (Blueprint $table) {
            $table->string('parent_name')->nullable()->after('nis');
            $table->string('school_level', 20)->nullable()->after('parent_name');
            $table->string('track', 20)->default('alquran')->after('school_level');
            $table->unsignedTinyInteger('iqro_level')->nullable()->after('track');
            $table->string('photo_path')->nullable()->after('iqro_level');
        });
    }

    public function down(): void
    {
        Schema::table('santri_profiles', function (Blueprint $table) {
            $table->dropColumn(['parent_name', 'school_level', 'track', 'iqro_level', 'photo_path']);
        });

        Schema::dropIfExists('santri_registrations');
    }
};
