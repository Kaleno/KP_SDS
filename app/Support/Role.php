<?php

namespace App\Support;

final class Role
{
    public const SuperAdmin = 'super_admin';

    public const Ketua = 'ketua';

    public const Ustaz = 'ustaz';

    public const Santri = 'santri';

    public const OrangTua = 'orang_tua';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::SuperAdmin,
            self::Ketua,
            self::Ustaz,
            self::Santri,
            self::OrangTua,
        ];
    }

    public static function label(string $role): string
    {
        return match ($role) {
            self::SuperAdmin => 'Super Admin',
            self::Ketua => 'Ketua',
            self::Ustaz => 'Ustaz',
            self::Santri => 'Santri',
            self::OrangTua => 'Orang Tua',
            default => $role,
        };
    }
}
