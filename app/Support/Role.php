<?php

namespace App\Support;

final class Role
{
    public const SuperAdmin = 'super_admin';

    public const Ketua = 'ketua';

    public const KetuaPengajar = 'ketua_pengajar';

    public const Pengajar = 'pengajar';

    public const Santri = 'santri';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::SuperAdmin,
            self::Ketua,
            self::KetuaPengajar,
            self::Pengajar,
            self::Santri,
        ];
    }

    /**
     * Role yang boleh absensi & penilaian.
     *
     * @return list<string>
     */
    public static function teaching(): array
    {
        return [
            self::KetuaPengajar,
            self::Pengajar,
        ];
    }

    /**
     * Role yang boleh absensi, penilaian, laporan, dan (nanti) SPP/jadwal.
     *
     * @return list<string>
     */
    public static function teachingLeaders(): array
    {
        return [
            self::KetuaPengajar,
        ];
    }

    public static function label(string $role): string
    {
        return match ($role) {
            self::SuperAdmin => 'System Admin',
            self::Ketua => 'Ketua DKM',
            self::KetuaPengajar => 'Ketua Pengajar',
            self::Pengajar => 'Pengajar',
            self::Santri => 'Santri',
            default => $role,
        };
    }

    public static function isTeaching(string $role): bool
    {
        return in_array($role, self::teaching(), true);
    }
}
