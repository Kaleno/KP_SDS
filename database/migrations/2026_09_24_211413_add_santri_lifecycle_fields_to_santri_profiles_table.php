<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santri_profiles', function (Blueprint $table): void {
            $table->text('address')->nullable()->after('parent_name');
            $table->date('joined_at')->nullable()->after('status');
            $table->date('graduated_at')->nullable()->after('joined_at');
            $table->date('spp_obligation_from')->nullable()->after('graduated_at');
        });

        $rows = DB::table('santri_profiles')->orderBy('id')->get(['id', 'status', 'created_at']);

        foreach ($rows as $row) {
            $joined = $row->created_at
                ? substr((string) $row->created_at, 0, 10)
                : now()->toDateString();

            DB::table('santri_profiles')->where('id', $row->id)->update([
                'joined_at' => $joined,
                'graduated_at' => $row->status === 'lulus' ? $joined : null,
                'spp_obligation_from' => $joined,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('santri_profiles', function (Blueprint $table): void {
            $table->dropColumn(['address', 'joined_at', 'graduated_at', 'spp_obligation_from']);
        });
    }
};
