<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('nip', 50)->nullable()->unique()->after('username');
            $table->date('birth_date')->nullable()->after('phone');
            $table->text('address')->nullable()->after('birth_date');
            $table->string('education_level', 20)->nullable()->after('address');
            $table->string('photo_path')->nullable()->after('education_level');
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['nip']);
            $table->dropSoftDeletes();
            $table->dropColumn(['nip', 'birth_date', 'address', 'education_level', 'photo_path']);
        });
    }
};
