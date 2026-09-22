<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spp_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri_profiles')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedInteger('amount')->default(25000);
            $table->date('paid_at');
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['santri_id', 'year', 'month']);
        });

        Schema::create('finance_entries', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->string('source', 20)->default('manual');
            $table->unsignedInteger('amount');
            $table->date('entry_date');
            $table->string('category')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('spp_payment_id')->nullable()->constrained('spp_payments')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['type', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_entries');
        Schema::dropIfExists('spp_payments');
    }
};
