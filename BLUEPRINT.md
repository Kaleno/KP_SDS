# Blueprint Ringkas: Sistem Monitoring Hafalan, Absensi, & Penjadwalan Santri

## Tujuan Program

Memfasilitasi digitalisasi manajemen akademik pesantren/madrasah, mencakup pencatatan setoran hafalan Al-Quran harian, absensi kehadiran santri, pengelolaan kelompok halaqah, dan pengaturan jadwal pengajar secara terpusat dan mobile-friendly.

## Lingkungan Pengembangan (Local)

Laragon (Apache / MySQL / PHP).

## Tech Stack Utama

| Komponen | Teknologi |
| --- | --- |
| Backend & UI | Laravel (Blade) |
| Styling | Tailwind CSS (Responsive Mobile UI) |
| Database | MySQL (Relasional) |
| Otorisasi | Spatie Laravel Permission + Policy |

## Hak Akses (Role-Based Access Control)

| Role | Tanggung Jawab |
| --- | --- |
| **Super Admin** | Pemelihara teknis sistem dan pembuat akun awal Ketua (di luar operasional harian). |
| **Ketua** (Pimpinan/Mudir) | Pengendali manajerial: CRUD Ustaz/Santri/Orang Tua, pembagian kelompok halaqah, jadwal pengajar, laporan global. |
| **Ustaz** (Pengajar) | Eksekutor harian: absensi santri dan input setoran hafalan di halaqahnya. |
| **Santri** | Melihat rekap hafalan pribadi, grafik kemajuan, absensi, dan jadwal. |
| **Orang Tua** | Memantau rekap absensi dan hafalan anak (satu akun bisa banyak anak, taut NIS). |

## Keputusan Domain yang Terkunci

1. **Progress juz** — hanya ayat berstatus `lancar`; `ulang` / `perbaikan` tidak menambah persen. Progress = union ayat unik yang sudah diterima, dipetakan ke 30 juz.
2. **Keanggotaan** — satu halaqah aktif per santri per tahun ajaran; pindah lewat mutasi (`ended_at`).
3. **Absensi** — terikat sesi jadwal (tanggal + slot), status `hadir` / `izin` / `sakit` / `alfa`.
4. **Pembuat akun** — Super Admin hanya seed sistem + akun Ketua. Ketua yang CRUD Ustaz, Santri, Orang Tua.
5. **Orang tua** — satu akun bisa memantau banyak anak; taut saat pendaftaran lewat NIS.
6. **Kalkulasi progress** — agregasi saat halaman dibuka (query), bukan job/queue.

Dokumen turunan:

- [docs/ERD.md](docs/ERD.md) — skema relasi dan aturan data
- [docs/MVP.md](docs/MVP.md) — daftar modul dan urutan build
- [docs/PLAN.md](docs/PLAN.md) — urutan pengerjaan sesi coding

---

## Penjelasan Alur & Cara Kerja Program

Aplikasi ini dirancang dengan konsep **closed-loop system** yang memisahkan tugas administratif dan operasional harian, sehingga alur kerja di lingkungan pesantren menjadi jauh lebih terstruktur.

### 1. Alur Perencanaan oleh Ketua

Sebelum kegiatan belajar mengajar dimulai, Ketua masuk ke sistem untuk membentuk kelompok **Halaqah** (misal: Halaqah Tahfidz A) dan menunjuk seorang Ustaz sebagai pembimbingnya, lalu menempatkan santri sebagai anggota. Ketua kemudian menyusun **Jadwal Pengajar** (hari, jam, dan lokasi) yang terikat dengan kelompok tersebut.

### 2. Eksekusi Harian oleh Ustaz

Setiap kali sesi mengaji berlangsung, Ustaz membuka menu **Absensi** dari slot jadwal hari itu, lalu mencatat kehadiran santri di halaqahnya. Setelah itu, Ustaz masuk ke modul **Setoran Hafalan**, memilih nama santri, lalu memasukkan detail surat, nomor ayat awal hingga akhir, serta status kelancaran (**Lancar**, **Ulang**, atau **Perbaikan**).

### 3. Pemantauan oleh Santri & Orang Tua

Begitu Ustaz menekan tombol simpan, data langsung masuk ke database MySQL. Progress juz dihitung saat halaman rekap dibuka (bukan antrian latar belakang). Santri dan Orang Tua yang masuk ke akun masing-masing dapat langsung melihat riwayat setoran terbaru, grafik pencapaian (progress bar juz), serta rekap kehadiran harian tanpa harus menunggu laporan fisik atau rekap manual di akhir bulan.
