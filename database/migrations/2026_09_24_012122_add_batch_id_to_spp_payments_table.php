<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spp_payments', function (Blueprint $table): void {
            $table->uuid('batch_id')->nullable()->after('id')->index();
        });

        $ids = DB::table('spp_payments')->whereNull('batch_id')->pluck('id');

        foreach ($ids as $id) {
            DB::table('spp_payments')
                ->where('id', $id)
                ->update(['batch_id' => (string) Str::uuid()]);
        }
    }

    public function down(): void
    {
        Schema::table('spp_payments', function (Blueprint $table): void {
            $table->dropColumn('batch_id');
        });
    }
};
