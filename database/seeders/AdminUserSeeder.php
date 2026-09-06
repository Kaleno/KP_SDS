<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Role;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::query()->updateOrCreate(
            ['username' => env('SEED_SUPER_ADMIN_USERNAME', 'superadmin')],
            [
                'name' => 'Super Admin',
                'email' => env('SEED_SUPER_ADMIN_EMAIL', 'superadmin@kp-sds.test'),
                'password' => env('SEED_SUPER_ADMIN_PASSWORD', 'password'),
                'is_active' => true,
            ],
        );
        $superAdmin->syncRoles([Role::SuperAdmin]);

        $ketua = User::query()->updateOrCreate(
            ['username' => env('SEED_KETUA_USERNAME', 'ketua')],
            [
                'name' => 'Ketua / Mudir',
                'email' => env('SEED_KETUA_EMAIL', 'ketua@kp-sds.test'),
                'password' => env('SEED_KETUA_PASSWORD', 'password'),
                'is_active' => true,
            ],
        );
        $ketua->syncRoles([Role::Ketua]);
    }
}
