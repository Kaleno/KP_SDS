# Rencana Pengerjaan — KP_SDS

Cara kerja: **satu jalur berurutan**, satu fase selesai (bisa didemo) baru fase berikutnya. Tidak memakai backlog paralel atau agent runner.

Acuan spek: `BLUEPRINT.md`, `docs/ERD.md`, `docs/MVP.md`.

## Keputusan alur kerja

| Opsi | Dipakai? | Alasan |
| --- | --- | --- |
| Checklist fase (dokumen ini) | Ya | Cukup sebagai antrian kerja; urutan sudah jelas |
| Backlog tiket paralel (banyak issue independen) | Tidak | Modul saling tergantung; tiket paralel akan nabrak migration/route/layout |
| Agent runner / beberapa agent sekaligus | Tidak | File Laravel (routes, policy, layout) hampir selalu bentrok jika ditulis bersamaan |
| Queue worker Laravel (`queue:work`) | Tidak | Progress dihitung saat halaman dibuka (keputusan #6) |

Kalau nanti ada sisa kerja yang **benar-benar independen** (misalnya seed data demo vs copy halaman login), baru pecah 2 tugas. Sampai fase 2 selesai, kerjakan serial.

## Aturan sesi coding

1. Kerjakan **satu item** di bawah, sampai bisa dibuka di browser Laragon.
2. Jangan mulai fase berikutnya jika kriteria selesai fase belum terpenuhi.
3. Layout mobile Ustaz dicek di viewport HP (lebar ~390px), bukan hanya desktop.
4. Setiap fase punya akun uji; jangan tunda seeder demo ke akhir.

Asumsi lokal: Laragon Apache + MySQL, virtual host mengarah ke `public/`.

---

## Fase 0 — Fondasi

**Kriteria selesai:** bisa login Super Admin dan Ketua; role Spatie terpasang; 114 surat + 30 juz ada di database.

- [x] `laravel new` di folder proyek, Breeze Blade, Tailwind
- [x] Koneksi `.env` ke MySQL Laragon, `migrate` sukses
- [x] Spatie Permission: 5 role (`super_admin`, `ketua`, `ustaz`, `santri`, `orang_tua`)
- [x] Seeder Super Admin + Ketua (password di `README` / `.env.example`, bukan di git)
- [x] Migration `users` disesuaikan (`username`, `email` nullable, `is_active`)
- [x] Migration `academic_years`, `locations`
- [x] Migration + seeder `quran_surahs`, `quran_juz`
- [x] Layout dasar (sidebar/nav per role, mobile: menu bawah atau drawer)
- [x] Halaman Super Admin: daftar akun Ketua, aktif/nonaktif — tanpa menu operasional

**Uji:** login Super Admin → lihat Ketua. Login Ketua → tidak bisa masuk menu Super Admin.

---

## Fase 1 — Master & halaqah

**Kriteria selesai:** Ketua bisa menyiapkan satu halaqah lengkap (ustaz, santri, orang tua taut NIS, jadwal) dan sistem menolak santri masuk dua halaqah aktif di tahun yang sama.

- [x] CRUD tahun ajaran (hanya satu `is_active`) dan lokasi
- [x] CRUD Ustaz (buat `users` + role)
- [x] CRUD Santri (`users.username` = NIS + `santri_profiles`)
- [x] CRUD Orang Tua + taut/lepas anak via NIS
- [x] CRUD Halaqah + pilih ustaz pembimbing
- [x] Anggota halaqah + mutasi (`ended_at` + baris baru)
- [x] Unique keanggotaan aktif per tahun ajaran (DB + validasi form)
- [x] CRUD jadwal mingguan (hari, jam, lokasi) terikat halaqah
- [x] Policy: hanya Ketua (dan Super Admin teknis) yang mengubah master
- [x] Seeder demo: 1 tahun ajaran, 1 lokasi, 1 ustaz, 3 santri, 1 orang tua (2 anak), 1 halaqah, 2 slot jadwal

**Uji:** coba masukkan santri yang sudah aktif ke halaqah lain → gagal. Mutasi → sukses. Orang tua dengan 2 NIS → dua anak terhubung.

---

## Fase 2 — Operasional harian (inti produk)

**Kriteria selesai:** dari HP, Ustaz membuka sesi hari ini, mengisi absensi H/I/S/A, menyimpan setoran, dan mengoreksi input salah.

- [x] Daftar slot jadwal hari ini untuk halaqah Ustaz
- [x] Buka `attendance_sessions` (unique jadwal + tanggal)
- [x] Form absensi: semua anggota aktif, wajib pilih status
- [x] Form setoran: santri, surat, ayat awal–akhir, status, catatan
- [x] Validasi ayat vs `ayah_count` surat
- [x] Riwayat sesi + riwayat setoran halaqah
- [x] Koreksi absensi/setoran + `correction_note`
- [x] Policy: Ustaz hanya halaqahnya; Ketua bisa semua
- [x] UI satu kolom, tombol besar, tanpa tabel lebar

**Uji:** login Ustaz di viewport mobile → absen 3 santri (campur status) → setor 1 lancar, 1 ulang → edit setoran. Login Ustaz lain / tanpa halaqah → tidak melihat data itu.

---

## Fase 3 — Progress & laporan

**Kriteria selesai:** progress juz sesuai rumus union ayat `lancar`; dashboard Ketua menampilkan angka hari ini.

- [x] Service `HafalanProgress` (bukan job): union ayat lancar × peta juz
- [x] Halaman progress per santri (30 bar + persen total)
- [x] Filter riwayat setoran
- [x] Rekap absensi per santri / halaqah / rentang tanggal
- [x] Dashboard Ketua: halaqah, santri aktif, setoran hari ini, alfa hari ini
- [x] Uji rumus: setor tumpang tindih + status ulang tidak menaikkan persen; murajaah lancar tidak dobel-hitung ayat

**Uji:** setor Al-Fatihah 1–7 lancar → juz 1 naik. Setor ayat yang sama lagi lancar → persen tidak naik. Setor ulang di ayat baru → persen tidak naik.

---

## Fase 4 — Portal Santri & Orang Tua

**Kriteria selesai:** Santri dan Orang Tua hanya membaca data yang sama dengan yang diinput Ustaz; tidak ada form input.

- [x] Beranda Santri: progress, setoran terakhir, absensi, jadwal
- [x] Beranda Orang Tua: pilih anak jika lebih dari satu, lalu layar yang sama
- [x] Policy read-only; tidak ada route store/update untuk role ini
- [x] Cek kebocoran: orang tua A tidak melihat anak yang tidak ditautkan

**Uji:** setelah setoran Ustaz, refresh akun Santri dan Orang Tua → data muncul tanpa delay antrian.

---

## Urutan yang tidak boleh dilompati

```text
Fase 0  →  Fase 1  →  Fase 2  →  Fase 3  →  Fase 4
fondasi     master      absen+setor   grafik      portal
```

Fase 3 butuh data nyata dari fase 2. Fase 4 hanya skin read-only dari fase 3. Mengerjakan portal lebih dulu hanya menghasilkan halaman kosong.

## Definisi “selesai proyek MVP”

Closed-loop terdemo dalam satu cerita:

1. Ketua siapkan halaqah + jadwal
2. Ustaz absen dan setor dari HP
3. Ketua lihat dashboard + progress juz
4. Santri dan orang tua lihat rekap yang sama

Tanpa PWA, tanpa WhatsApp, tanpa queue.
