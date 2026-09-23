<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SPP payments no longer auto-create kas entries; purge legacy auto rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('finance_entries')) {
            return;
        }

        DB::table('finance_entries')->where('source', 'spp')->delete();
    }

    public function down(): void
    {
        // Irreversible cleanup of auto-generated SPP kas rows.
    }
};
