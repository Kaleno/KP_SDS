<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['ketua_pengajar', 'pengajar'] as $name) {
            Role::findOrCreate($name, 'web');
        }

        $ustaz = Role::query()->where('name', 'ustaz')->where('guard_name', 'web')->first();
        if ($ustaz) {
            $ketuaPengajar = Role::findByName('ketua_pengajar', 'web');

            DB::table('model_has_roles')
                ->where('role_id', $ustaz->id)
                ->update(['role_id' => $ketuaPengajar->id]);

            $ustaz->delete();
        }

        $orangTua = Role::query()->where('name', 'orang_tua')->where('guard_name', 'web')->first();
        if ($orangTua) {
            DB::table('model_has_roles')->where('role_id', $orangTua->id)->delete();
            $orangTua->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['ustaz', 'orang_tua'] as $name) {
            Role::findOrCreate($name, 'web');
        }

        $ketuaPengajar = Role::query()->where('name', 'ketua_pengajar')->where('guard_name', 'web')->first();
        $ustaz = Role::findByName('ustaz', 'web');

        if ($ketuaPengajar) {
            DB::table('model_has_roles')
                ->where('role_id', $ketuaPengajar->id)
                ->update(['role_id' => $ustaz->id]);
            $ketuaPengajar->delete();
        }

        Role::query()->where('name', 'pengajar')->where('guard_name', 'web')->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
