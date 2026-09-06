# KP_SDS

Sistem data santri: monitoring hafalan, absensi, dan penjadwalan pengajar.

- [BLUEPRINT.md](BLUEPRINT.md) — visi, role, alur, keputusan domain
- [docs/ERD.md](docs/ERD.md) — skema database
- [docs/MVP.md](docs/MVP.md) — modul dan urutan build
- [docs/PLAN.md](docs/PLAN.md) — rencana pengerjaan

## Menjalankan (lokal)

Laragon: Apache + MySQL. Database `kp_sds`, salin `.env.example` ke `.env` jika belum ada.

```bash
composer install
npm install
npm run build
php artisan migrate --seed
php artisan serve
```

Buka [http://kp_sds.test:8080](http://kp_sds.test:8080) (Nginx Laragon). Document root virtual host mengarah ke folder `public/`.

## Akun uji

Password default ada di `.env` (`SEED_*`). Nilai awal:

| Role | Username | Password |
| --- | --- | --- |
| Super Admin | `superadmin` | `password` |
| Ketua | `ketua` | `password` |
| Ustaz | `ustaz1` | `password` |
| Santri | `2026001` / `2026002` / `2026003` | `password` |
| Orang Tua | `ortu1` | `password` |

Login memakai username atau email. Pendaftaran publik dimatikan. Orang tua `ortu1` tertaut ke dua santri pertama.
