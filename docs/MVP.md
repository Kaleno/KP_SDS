# Daftar Modul MVP — KP_SDS

Tujuan MVP: closed-loop **perencanaan → absensi/setoran → pantau progress** jalan di satu tahun ajaran, mobile-friendly di browser (bukan PWA).

## Fase dan keluaran

| Fase | Nama | Masuk MVP? | Keluaran yang bisa didemo |
| --- | --- | --- | --- |
| 0 | Fondasi | Ya | Laravel, auth, role, seeder Super Admin + Ketua, seed 114 surat + 30 juz |
| 1 | Master & halaqah | Ya | Ketua kelola orang, kelompok, jadwal |
| 2 | Operasional harian | Ya | Ustaz absen + setor hafalan dari HP |
| 3 | Progress & laporan | Ya | Progress bar juz + rekap Ketua |
| 4 | Portal Santri & Orang Tua | Ya (setelah 1–3 stabil) | Read-only, data yang sama dengan fase 2–3 |

Fase 4 tetap MVP karena ada di tujuan program, tetapi **jangan dikerjakan paralel** dengan fase 1–2.

## Modul per fase

### Fase 0 — Fondasi

| Modul | Isi | Role |
| --- | --- | --- |
| Auth | Login, logout, ganti password | semua |
| RBAC | Spatie Permission + Policy per resource | semua |
| Super Admin | Nonaktifkan/aktifkan akun Ketua; tidak ada menu absensi/setoran | Super Admin |
| Seeder Quran | `quran_surahs` (114) + `quran_juz` (30, mushaf Madinah) | sistem |
| Tahun ajaran & lokasi | CRUD; hanya satu tahun `is_active` | Ketua |

### Fase 1 — Master & halaqah

| Modul | Isi | Role |
| --- | --- | --- |
| Pengajar | CRUD akun Ustaz | Ketua |
| Santri | CRUD akun + profil + NIS | Ketua |
| Orang Tua | CRUD akun + taut/lepas anak via NIS | Ketua |
| Halaqah | Buat kelompok, tunjuk ustaz, aktif/nonaktif | Ketua |
| Anggota halaqah | Tambah santri; mutasi (tutup `ended_at`, buka baris baru) | Ketua |
| Jadwal | Slot mingguan: hari, jam, lokasi, terikat halaqah | Ketua |

Validasi fase 1: santri hanya boleh satu keanggotaan aktif per tahun ajaran.

### Fase 2 — Operasional harian (prioritas UX mobile)

| Modul | Isi | Role |
| --- | --- | --- |
| Buka sesi absensi | Pilih slot jadwal hari ini → generate baris anggota | Ustaz (halaqahnya), Ketua |
| Isi absensi | Status hadir / izin / sakit / alfa + catatan | Ustaz, Ketua |
| Setoran hafalan | Pilih santri → surat, ayat awal–akhir, status lancar/ulang/perbaikan | Ustaz, Ketua |
| Koreksi | Edit absensi/setoran yang sudah tersimpan + `correction_note` | Ustaz (miliknya), Ketua |
| Daftar sesi | Riwayat sesi halaqah | Ustaz, Ketua |

UI Ustaz: satu kolom, tap besar, tanpa tabel lebar. Bukan PWA; cukup responsive Blade di Safari/Chrome.

### Fase 3 — Progress & laporan

| Modul | Isi | Role |
| --- | --- | --- |
| Progress juz santri | 30 bar + persen total; rumus union ayat `lancar` | Ketua, Ustaz (anggotanya) |
| Riwayat setoran | Filter surat/status/tanggal | Ketua, Ustaz |
| Rekap absensi | Persen hadir per santri / per halaqah / rentang tanggal | Ketua, Ustaz |
| Dashboard Ketua | Jumlah halaqah, santri aktif, setoran hari ini, alfa hari ini | Ketua |

Progress dihitung saat halaman dibuka. Tidak ada job queue.

### Fase 4 — Portal pantau

| Modul | Isi | Role |
| --- | --- | --- |
| Beranda santri | Progress juz, setoran terakhir, absensi terkini, jadwal halaqah | Santri |
| Beranda orang tua | Pilih anak (jika >1) → layar yang sama seperti santri, read-only | Orang Tua |

Tidak ada input dari Santri/Orang Tua di MVP.

## Di luar MVP (tunda)

- PWA / service worker / Add to Home Screen (lihat catatan iOS di bawah)
- Mode offline + antrian absensi
- Notifikasi WhatsApp / Web Push
- Ustaz pengganti per sesi
- Multi-pesantren
- Export PDF/Excel (boleh ditambah jika sisa waktu)
- Grafik library berat; cukup progress bar + daftar

## Catatan PWA vs iOS (keputusan produk)

PWA **didukung iOS**, tetapi bukan setara Android: tidak ada prompt install otomatis, push hanya iOS 16.4+ setelah Add to Home Screen, tidak ada background sync. Untuk KP ini, situs Laravel responsive sudah cukup di iPhone. PWA tidak masuk urutan build.

## Urutan scaffolding Laravel yang disarankan

1. `laravel new` + Breeze Blade + Tailwind
2. Spatie Permission + seeder role + Super Admin + Ketua
3. Migration sesuai `docs/ERD.md` (tahun ajaran → users/profil → halaqah → jadwal → absensi → setoran → seed quran)
4. Policy, lalu CRUD fase 1
5. Layar mobile fase 2
6. Query progress fase 3
7. Portal read-only fase 4
