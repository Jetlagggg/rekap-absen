# Rekap Absen

Aplikasi web rekap absensi dan perhitungan TPP (Tambahan Penghasilan Pegawai) untuk Dinas Pekerjaan Umum dan Penataan Ruang Kabupaten Kutai Barat.

![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)
![Laravel 12](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![PHPUnit](https://img.shields.io/badge/tested%20with-PHPUnit-3C9CD7?logo=php&logoColor=white)
![SQLite](https://img.shields.io/badge/database-SQLite-003B57?logo=sqlite&logoColor=white)

> Proyek internal untuk mengelola kehadiran pegawai dan menghitung potongan TPP berdasarkan aturan yang berlaku di Dinas PUPR Kabupaten Kutai Barat.

## Daftar Isi

- [Fitur](#fitur)
- [Role dan Hak Akses](#role-dan-hak-akses)
- [Teknologi](#teknologi)
- [Persyaratan](#persyaratan)
- [Instalasi](#instalasi)
- [Perintah Penting](#perintah-penting)
- [Aturan Perhitungan TPP](#aturan-perhitungan-tpp)
- [Struktur Proyek](#struktur-proyek)
- [Testing](#testing)
- [Audit dan Roadmap](#audit-dan-roadmap)
- [CI/CD](#cicd)
- [Deployment](#deployment)
- [Keamanan](#keamanan)
- [Kontribusi](#kontribusi)
- [Lisensi](#lisensi)

## Fitur

- **Autentikasi dan sesi** — login/logout, rate limit 5 percobaan per menit, akun nonaktif otomatis ditolak, dan tidak ada registrasi publik.
- **Dashboard** — total pegawai, kehadiran hari ini, keterlambatan hari ini, dan disiplin bulan berjalan.
- **Input absensi massal** — per tanggal, dengan status hadir, sakit, izin, cuti, dinas luar, dan alpha.
- **Rekap kinerja/TPP bulanan** — jumlah hadir, telat, sakit, izin, cuti, alpha, dinas luar, total potongan, dan skor kinerja.
- **Unduh PDF** — laporan rekap A4 landscape lengkap dengan kop instansi.
- **Kelola pegawai** — tambah, edit, hapus, dan import CSV (`nip,nama,jabatan`).
- **Panel admin pengguna** — tambah akun, ubah nama/email, atur role, aktif/nonaktifkan akun, dan reset kata sandi.

## Role dan Hak Akses

| Role | Akses |
| --- | --- |
| **Admin** | Seluruh fitur aplikasi, termasuk panel pengguna `/admin/users`. |
| **Operator** | Dashboard, input absensi, kelola pegawai, dan laporan. Tidak dapat membuka panel pengguna. |

Akun admin pertama dibuat melalui perintah artisan (lihat [Instalasi](#instalasi)). Admin tidak dapat menurunkan role atau menonaktifkan akun sendiri, dan sistem menjaga agar minimal satu administrator aktif tetap tersedia.

## Teknologi

- **Backend:** Laravel 12, PHP 8.2
- **Database:** SQLite (default), mendukung konfigurasi database Laravel lain seperti MySQL/PostgreSQL
- **Frontend:** Blade, Bootstrap 5 dan Bootstrap Icons via CDN, CSS khusus halaman; scaffolding Vite 7 + Tailwind CSS 4 tersedia untuk pengembangan aset
- **PDF:** `barryvdh/laravel-dompdf`
- **Testing:** PHPUnit 11 dan Playwright (E2E)
- **CI/CD:** GitHub Actions
- **Format kode:** Laravel Pint (PSR-12)

## Persyaratan

- PHP >= 8.2 dengan ekstensi standar Laravel (`pdo_sqlite` atau driver database lain, `mbstring`, `openssl`, `dom`, `gd`)
- Composer 2
- Node.js 20.19+ atau 22.12+ dan npm (untuk `npm run build` / `npm run dev`)
- Git

## Instalasi

### 1. Clone repositori

```bash
git clone https://github.com/Jetlagggg/rekap-absen.git rekap-absen
cd rekap-absen
```

### 2. Siapkan database SQLite

```bash
# Linux/macOS
touch database/database.sqlite

# Windows PowerShell
New-Item -ItemType File -Path database\database.sqlite -Force
```

File database lokal tidak ikut di-commit karena sudah tercantum di `.gitignore`.

### 3. Setup aplikasi

Cara cepat (sekali jalan):

```bash
composer run setup
```

Perintah tersebut menjalankan `composer install`, menyalin `.env`, membuat `APP_KEY`, migrasi database, `npm install`, dan `npm run build`.

Atau lakukan langkah manual:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

Sesuaikan `.env` bila perlu, misalnya `APP_NAME="Rekap Absen"`, `APP_URL=http://127.0.0.1:8000`, dan kredensial database.

### 4. Buat akun admin pertama

```bash
php artisan app:create-admin admin@example.com --name="Administrator"
```

Perintah akan meminta kata sandi dua kali. Kata sandi minimal 12 karakter. Tidak ada halaman registrasi publik.

### 5. Jalankan aplikasi

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000`, lalu masuk memakai akun admin yang baru dibuat. Halaman aplikasi memuat Bootstrap dari CDN, jadi server perlu akses internet. Untuk pengembangan dengan Vite:

```bash
composer run dev
```

## Perintah Penting

| Tujuan | Perintah |
| --- | --- |
| Setup awal | `composer run setup` |
| Menjalankan server | `php artisan serve` |
| Frontend dev | `npm run dev` |
| Build aset | `npm run build` |
| Migrasi database | `php artisan migrate` |
| Membuat/mengatur ulang akun admin | `php artisan app:create-admin` |
| Semua test | `php artisan test` |
| Test unit | `php artisan test --testsuite=Unit` |
| Test fitur | `php artisan test --testsuite=Feature` |
| Test E2E | `npm run e2e` |
| Pasang browser E2E | `npm run e2e:install` |
| Test E2E mode UI | `npm run e2e:ui` |
| Test spesifik | `php artisan test --filter=AdminUserTest` |
| Format kode | `vendor/bin/pint` |
| Bersihkan cache | `php artisan optimize:clear` |

Seeder bawaan tidak membuat akun. Seeder pegawai/TPP bersifat lokal dan melakukan `truncate`, jadi jangan menjalankan seeder di database produksi; akun admin dibuat melalui `php artisan app:create-admin` atau panel admin.

## Aturan Perhitungan TPP

- Toleransi jam masuk sampai **08:10**.
- Jam pulang standar: **11:00** hari Jumat, **16:00** hari lain.
- Hadir mencakup dinas luar; keterlambatan dihitung dari status hadir dengan `terlambat_menit > 0`.

| Kondisi | Potongan |
| --- | --- |
| Terlambat 1–30 menit | 0,5% |
| Terlambat 31–60 menit | 1,0% |
| Terlambat 61–90 menit | 1,25% |
| Terlambat lebih dari 90 menit | 1,5% |
| Pulang cepat | 1,0% |
| Lupa absen pulang | 1,5% |
| Alpha | 3,0% |
| Izin | 1,75% |
| Sakit, cuti, dinas luar | 0% |

- Nominal potongan = `total_potongan_persen / 100 × (40% dari tpp_pagu)`.
- Total potongan dibatasi maksimal **100%**; skor kinerja = **100 − total potongan**.
- Rekap web dan PDF memakai sumber perhitungan yang sama (`generateRekap`) agar angkanya konsisten.

## Struktur Proyek

```text
app/
├── Console/Commands/CreateAdmin.php
├── Http/Controllers/
│   ├── Admin/UserController.php
│   ├── AttendanceController.php
│   └── Auth/LoginController.php
├── Http/Middleware/
│   ├── EnsureUserIsActive.php
│   └── EnsureUserIsAdmin.php
└── Models/
    ├── Attendance.php
    ├── Employee.php
    ├── TppRule.php
    └── User.php
database/
├── factories/UserFactory.php
├── migrations/
└── seeders/
resources/views/
├── admin/users/
├── attendance/
├── auth/
└── layouts/app.blade.php
routes/web.php
tests/
├── Feature/
├── Unit/
└── e2e/          # Playwright
playwright.config.js
.github/workflows/ci-cd.yml
```

## Testing

### Unit dan feature test

```bash
php artisan test
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

Test PHPUnit memakai SQLite `:memory:` dan `RefreshDatabase`. Aturan potongan TPP diuji unit melalui `AttendancePenaltyCalculator`; alur HTTP, validasi, dan panel admin diuji sebagai feature test. Perubahan pada perhitungan TPP, validasi absensi, import pegawai, rekap, role, atau status akun wajib disertai test.

### End-to-end test

```bash
npm install
npm run e2e:install   # unduh Chromium untuk Playwright
npm run e2e
npm run e2e:ui        # mode interaktif
```

E2E memakai environment terpisah `e2e`:

- `.env.e2e` berisi `APP_KEY` khusus pengujian; jangan dipakai untuk produksi.
- Database `database/e2e.sqlite` dibuat ulang setiap kali suite dijalankan.
- `database/seeders/E2eSeeder.php` hanya berjalan pada environment `e2e` dan membuat akun `admin@e2e.test` / `operator@e2e.test` beserta dua pegawai contoh.
- Playwright menjalankan `php artisan serve` pada port `9123` secara otomatis.

Skenario E2E mencakup login/logout, penolakan kredensial salah, input absensi massal, rekap TPP, pembuatan operator, pembatasan akses panel admin, serta tambah/cari/hapus pegawai.

Jalankan `vendor/bin/pint` sebelum mengirim perubahan.

## Audit dan Roadmap

- [`AUDIT.md`](AUDIT.md) — hasil audit keamanan, integritas data, logika TPP, performa, frontend, dan operasional, lengkap dengan bukti file/baris serta prioritas.
- [`ROADMAP.md`](ROADMAP.md) — urutan perbaikan per fase, ukuran selesai, dan backlog jangka panjang.
- [`GITHUB_ISSUES.md`](GITHUB_ISSUES.md) — 13 draf issue siap disalin ke GitHub beserta label dan kriteria selesai.

Temuan audit belum otomatis menjadi GitHub Issue. Pembuatan issue dilakukan setelah isi dan prioritasnya disetujui pengelola.

## CI/CD

Workflow `.github/workflows/ci-cd.yml` berjalan pada push ke `main`, pull request, dan manual dispatch:

1. **Code style** — `vendor/bin/pint --test`.
2. **Unit & feature tests** — PHP 8.2 dan 8.3.
3. **Build frontend** — `npm ci` dan `npm run build`; artifact `public/build` diunggah.
4. **E2E** — Playwright dengan Chromium headless.
5. **Deploy** — hanya pada push/manual dispatch ke `main` setelah semua job lulus.

### Menyiapkan deploy VPS

Tambahkan secrets berikut di **Settings → Secrets and variables → Actions**:

| Secret | Keterangan |
| --- | --- |
| `DEPLOY_HOST` | Host atau IP VPS. |
| `DEPLOY_USER` | Pengguna SSH yang memiliki akses ke direktori aplikasi. |
| `DEPLOY_SSH_KEY` | Private key SSH (tanpa passphrase atau gunakan agent). |
| `DEPLOY_PATH` | Path absolut checkout aplikasi di VPS, misalnya `/var/www/rekap-absen`. |
| `DEPLOY_PORT` | Opsional; default `22`. |

Variabel opsional `APP_URL` dapat diisi pada **Settings → Secrets and variables → Actions → Variables** untuk menampilkan tautan environment produksi.

Server tujuan perlu memiliki PHP 8.2+, Composer, Node.js/npm, Git, dan database yang dikonfigurasi pada `.env` produksi. Direktori `DEPLOY_PATH` harus berupa checkout Git dengan remote `origin`, serta `storage/` dan `bootstrap/cache/` dapat ditulis oleh pengguna SSH. Skrip deploy mengaktifkan mode maintenance, menyelaraskan kode dengan `origin/main`, memasang dependensi, menjalankan migrasi, membangun aset, lalu mengoptimalkan cache. Jangan menaruh kredensial di workflow; gunakan secrets.

## Deployment

1. Siapkan `.env` produksi:

   ```dotenv
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://domain-anda.example
   ```

2. Pastikan `APP_KEY` sudah dibuat (`php artisan key:generate`).
3. Arahkan web server ke folder `public/`, dan pastikan `storage/` serta `bootstrap/cache/` dapat ditulis.
4. Jalankan migrasi dan optimasi:

   ```bash
   php artisan migrate --force
   php artisan optimize
   ```

5. Buat akun admin melalui `php artisan app:create-admin` (bukan lewat seeder).
6. Jangan commit `.env`, `database/*.sqlite`, atau kredensial ke repositori.

## Keamanan

- Tidak ada registrasi publik; akun dibuat melalui perintah artisan atau panel admin.
- Kata sandi disimpan sebagai hash; tidak ada kata sandi plaintext yang ditampilkan atau disimpan.
- Login dibatasi 5 percobaan per menit dan memakai pesan error generik.
- Semua route aplikasi dilindungi middleware `auth` dan `active`; panel pengguna juga memakai middleware `admin`.
- Akun nonaktif tidak dapat login dan sesi aktifnya dihentikan otomatis.
- Reset kata sandi minimal 12 karakter, wajib dikonfirmasi, dan mencabut remember token pengguna.

Laporkan masalah keamanan kepada pengelola proyek secara privat, bukan melalui issue publik.

## Kontribusi

1. Baca `AGENTS.md` sebelum mengubah kode.
2. Mulai dari `main` yang sinkron, lalu buat worktree dan branch fitur; jangan bekerja langsung di `main`.
3. Buat perubahan sekecil mungkin dan ikuti pola yang sudah ada.
4. Tambahkan/perbarui test untuk perubahan perilaku.
5. Catat setiap perubahan di bagian `[Unreleased]` pada `CHANGELOG.md`.
6. Jalankan `php artisan test`, test E2E bila UI berubah, dan `vendor/bin/pint --test`.
7. Ajukan PR ke `main`. Review, merge, dan squash dilakukan oleh pemilik proyek, bukan oleh agen.
8. Jangan pernah push langsung ke `main`, force-push, atau membuat issue yang memuat data pegawai/kredensial tanpa persetujuan.

## Lisensi

Proyek ini dikembangkan untuk kebutuhan internal Dinas PUPR Kabupaten Kutai Barat. `composer.json` menandai lisensi MIT; tambahkan berkas `LICENSE` jika proyek akan dipublikasikan sebagai open source.