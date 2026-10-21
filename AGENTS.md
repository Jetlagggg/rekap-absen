# AGENTS.md

Panduan kerja untuk agen AI dan kontributor pada proyek **Rekap Absen**.

> **Aturan mutlak:** setiap perubahan wajib dicatat di `CHANGELOG.md` bagian `[Unreleased]` pada tugas yang sama. Tugas belum selesai jika changelog belum diperbarui.

## Tentang Proyek

Aplikasi rekap absensi dan perhitungan TPP (Tambahan Penghasilan Pegawai) untuk Dinas PUPR Kabupaten Kutai Barat.

- **Backend:** Laravel 12, PHP 8.2
- **Database:** SQLite (`database/database.sqlite`), dengan dukungan konfigurasi Laravel lain
- **Frontend:** Blade + Tailwind CSS 4 + Vite 7 (tanpa framework JS SPA)
- **PDF:** `barryvdh/laravel-dompdf`, A4 landscape
- **Testing:** PHPUnit 11 (unit/feature) dan Playwright (E2E)

Fitur utama: dashboard, input absensi massal per tanggal, rekap kinerja/TPP bulanan, unduh PDF, kelola pegawai (tambah, edit, hapus, import CSV), serta panel admin untuk mengelola pengguna, role, status akun, dan reset kata sandi.

## Perintah Penting

| Tujuan | Perintah |
| --- | --- |
| Setup awal | `composer run setup` |
| Menjalankan server | `php artisan serve` |
| Frontend dev | `npm run dev` |
| Build aset | `npm run build` |
| Migrasi database | `php artisan migrate` |
| Seeder | `php artisan db:seed` |
| Membuat akun admin | `php artisan app:create-admin` |
| Semua test PHPUnit | `php artisan test` atau `composer test` |
| Test unit | `php artisan test --testsuite=Unit` |
| Test fitur | `php artisan test --testsuite=Feature` |
| Pasang browser E2E | `npm run e2e:install` |
| Test E2E | `npm run e2e` |
| Test E2E mode UI | `npm run e2e:ui` |
| Test spesifik | `php artisan test --filter=NamaTest` |
| Format kode | `vendor/bin/pint` |
| Cek format seperti CI | `vendor/bin/pint --test` |
| Bersihkan cache | `php artisan optimize:clear` |

Selalu jalankan test setelah mengubah logika perhitungan, validasi, import, atau route. Jalankan `npm run e2e` jika mengubah alur UI atau autentikasi, dan `npm run build` jika mengubah aset frontend.

## Aturan Wajib: Catat Setiap Perubahan di CHANGELOG.md

Aturan ini berlaku untuk **semua** perubahan, termasuk kode, UI, skema database, konfigurasi, dokumentasi, dan `AGENTS.md` itu sendiri.

1. Tambahkan entri pada bagian `[Unreleased]` di tugas yang sama; jangan menunda ke tugas berikutnya.
2. Gunakan kategori sesuai Keep a Changelog: `Ditambahkan`, `Diubah`, `Diperbaiki`, `Dihapus`, `Keamanan`.
3. Tulis dalam bahasa Indonesia yang ringkas dan berorientasi dampak: apa yang berubah dan mengapa.
4. Jangan menghapus atau menulis ulang entri lama.
5. Saat merilis, pindahkan entri `[Unreleased]` ke versi baru dengan format `## [x.y.z] - YYYY-MM-DD` dan ikuti Semantic Versioning.
6. Perubahan pada `AGENTS.md` juga wajib dicatat di changelog.
7. Tugas dianggap belum selesai jika `CHANGELOG.md` belum diperbarui. Jika tidak ada perubahan yang layak dicatat, jelaskan alasannya di respons akhir.

## Alur Kerja

1. Baca file terkait sebelum mengubah apa pun; jangan berasumsi.
2. Buat perubahan sekecil mungkin sesuai permintaan. Hindari refactor yang tidak diminta.
3. Ikuti pola dan gaya kode yang sudah ada.
4. Tambahkan atau perbarui test untuk perubahan perilaku.
5. Perbarui `CHANGELOG.md`.
6. Jangan melakukan commit, push, atau deploy tanpa permintaan eksplisit pengguna.

## Alur Git, PR, dan GitHub Issues

Aturan ini wajib dipatuhi untuk setiap tugas implementasi.

1. **Jangan pernah commit atau push langsung ke `main`.** `main` hanya diperbarui melalui PR yang di-merge oleh pengguna.
2. Mulai dari `main` lokal yang bersih dan sinkron dengan `origin/main`. Untuk tugas implementasi, buat worktree terisolasi, bukan bekerja di worktree utama:
   ```bash
   git fetch origin
   git worktree add ../rekap-absen-<slug> -b <tipe>/<slug> origin/main
   ```
   Gunakan `tipe` seperti `feat`, `fix`, `docs`, `test`, atau `chore`, dan slug singkat berhuruf kecil.
3. Lakukan seluruh perubahan, test, dan pembaruan `CHANGELOG.md` di worktree tersebut.
4. Commit dan push hanya ke branch fitur. Jangan pernah force-push, merge PR, atau push ke `main`. Jangan membuat commit kosong.
5. Buat PR ke `main` setelah pengguna meminta perubahan tersebut diselesaikan. Isi PR dengan ringkasan perubahan, test yang dijalankan, dan dampaknya.
6. Pengguna yang melakukan review, merge, dan squash. Agen tidak boleh menekan tombol merge atau menjalankan `gh pr merge`.
7. Jika ada masukan review, tambahkan commit baru pada branch dan PR yang sama; jangan membuat PR duplikat.
8. Setelah pengguna mengonfirmasi PR sudah di-merge, sinkronkan `main` lokal:
   ```bash
   git fetch origin
   git switch main
   git pull --ff-only origin main
   ```
   Worktree dan branch fitur boleh dihapus setelah merge dikonfirmasi dan tidak ada perubahan yang belum tersimpan.
9. **Bug, error, masalah, atau utang teknis** yang ditemukan tetapi berada di luar lingkup tugas dicatat di `AUDIT.md` dan `ROADMAP.md`. Buat GitHub Issue hanya setelah pengguna menyetujui isi dan prioritasnya; jangan membuka issue yang mengandung data pegawai, kredensial, atau detail sensitif.
10. Jika pengguna meminta issue dibuat, gunakan `gh issue create` dengan judul ringkas, langkah reproduksi, dampak, dan label yang sesuai. Hubungkan PR yang memperbaikinya dengan `Closes #<nomor>`.
11. Jika pengguna hanya meminta analisis, audit, atau penjelasan, jangan membuat commit, push, PR, atau issue.

## Struktur dan Konvensi Kode

- **Controller:** `app/Http/Controllers/AttendanceController.php` untuk absensi, pegawai, dan rekap/TPP; `app/Http/Controllers/Auth/LoginController.php` untuk autentikasi; `app/Http/Controllers/Admin/UserController.php` untuk panel pengguna. Tambahkan method pada controller terkait; hindari mencampur logika admin ke controller absensi.
- **Model:** `app/Models/{Attendance,Employee,TppRule,User}.php`.
- **Service:** `app/Services/AttendancePenaltyCalculator.php` adalah satu-satunya sumber perhitungan denda absensi; jangan menduplikasi tarif atau jam kerja di controller/view. Perubahan aturan wajib disertai unit test.
- **View:** `resources/views/attendance/` untuk halaman absensi dan `resources/views/admin/users/` untuk panel pengguna. Halaman memakai layout bersama `layouts/app.blade.php`; tambahkan CSS khusus halaman melalui `@push('styles')`.
- **Route:** `routes/web.php`, gunakan named route. Jangan mengubah URL atau nama route yang sudah ada tanpa kebutuhan yang jelas.
- **Aset:** `resources/css/app.css`, `resources/js/`, dan gambar di `public/img/`.
- **E2E:** Test Playwright berada di `tests/e2e/` dengan konfigurasi `playwright.config.js`. Gunakan `.env.e2e` dan `database/e2e.sqlite`; `E2eSeeder` hanya boleh berjalan pada environment `e2e`.
- **Migrasi:** Jangan mengubah migrasi yang sudah pernah dijalankan. Buat migrasi baru untuk perubahan skema.
- **Gaya:** PSR-12/Pint, indentasi 4 spasi, UTF-8, LF, baris baru di akhir file.
- **Bahasa:** Kolom domain absensi/pegawai memakai bahasa Indonesia (`nip`, `nama`, `jabatan`, `tpp_pagu`, `tanggal`, `status_kehadiran`); kolom autentikasi mengikuti konvensi Laravel (`email`, `password`, `role`, `is_active`). Teks antarmuka memakai bahasa Indonesia.
- **Dependensi:** Jangan menambah library baru tanpa memeriksa `composer.json` / `package.json` dan tanpa persetujuan pengguna.
- **Larangan:** Jangan mengedit isi `vendor/`, `node_modules/`, `.env`, atau `database/database.sqlite` secara manual. `.env.e2e` hanya berisi konfigurasi pengujian dan tidak boleh memakai kredensial produksi.

## Aturan Domain Absensi dan TPP

Gunakan konstanta status pada `App\Models\Attendance`; jangan menulis string status secara tersebar jika bisa memakai konstanta.

Status yang didukung: `hadir`, `sakit`, `izin`, `cuti`, `dinas luar`, `alpha`.

Aturan perhitungan saat ini:

- Toleransi jam masuk sampai **08:10**.
- Jam pulang standar: **11:00** hari Jumat, **16:00** hari lainnya.
- Terlambat: 1-30 menit **0,5%**; 31-60 menit **1,0%**; 61-90 menit **1,25%**; lebih dari 90 menit **1,5%**.
- Pulang cepat: **1,0%**; lupa absen pulang: **1,5%**.
- Alpha **3,0%**; izin **1,75%**; sakit, cuti, dan dinas luar **0%**.
- Nominal potongan = `total_potongan_persen / 100 * (40% dari tpp_pagu)`.
- Rekap bulanan: hadir mencakup dinas luar; telat dihitung dari status hadir dengan `terlambat_menit > 0`; total potongan dibatasi maksimal **100%**; skor kinerja = `100 - total potongan`.
- Rekap web dan PDF **wajib** memakai sumber perhitungan yang sama (`generateRekap`) agar angkanya konsisten.

Aturan penyimpanan:

- Absensi disimpan per kombinasi `(employee_id, tanggal)` dengan `updateOrCreate`.
- Penyimpanan massal harus berada dalam `DB::transaction`.
- Relasi absensi ke pegawai memakai cascade delete.
- Import CSV memakai format `nip,nama,jabatan`; baris header dan baris dengan kolom kurang dari tiga dilewati.

**Jangan mengubah aturan perhitungan, tarif potongan, jam kerja, atau format import tanpa konfirmasi eksplisit pengguna.**

## Validasi dan Keamanan

- Semua route aplikasi dilindungi middleware `auth` dan `active`; route `/admin/users` juga memakai middleware `admin`. Jangan menambahkan registrasi publik. Akun dibuat melalui `php artisan app:create-admin` atau panel admin oleh pengguna berrole admin.
- Role yang didukung: `admin` dan `operator`. Hanya admin yang boleh mengelola pengguna; operator tidak boleh mengakses panel pengguna.
- Jangan menampilkan atau menyimpan kata sandi plaintext. Reset kata sandi minimal 12 karakter dan wajib dikonfirmasi; admin tidak boleh melihat kata sandi lama.
- Admin tidak boleh menurunkan role atau menonaktifkan akun sendiri, dan minimal satu administrator aktif harus tersedia.
- Akun nonaktif tidak boleh login; sesi aktif akun nonaktif dihentikan oleh middleware `active`.
- Semua input pengguna wajib divalidasi melalui validasi Laravel di controller.
- NIP wajib dan unik saat menambah pegawai.
- Gunakan `findOrFail` atau route model binding untuk data yang diedit/dihapus.
- Jangan mengekspos `.env`, `APP_KEY`, kredensial, atau data pegawai di log.
- Jangan menjalankan seeder yang melakukan `truncate` pada database non-lokal.
- Jangan menghapus data pengguna atau file di luar lingkup tugas.

## Testing

- Test PHPUnit berada di `tests/Feature` dan `tests/Unit`; test E2E berada di `tests/e2e` memakai Playwright. Proyek tidak memakai Pest.
- Konfigurasi PHPUnit memakai SQLite `:memory:`; gunakan `RefreshDatabase` untuk test yang menyentuh database.
- Semua route aplikasi dilindungi `auth` dan `active`; test fitur yang memanggil route aplikasi wajib memakai `actingAs(User::factory()->create())`, kecuali test autentikasi.
- `User::factory()` membuat operator; untuk test route admin, buat pengguna dengan `role => User::ROLE_ADMIN`.
- Setiap perubahan pada perhitungan TPP, validasi absensi, import pegawai, rekap, role, atau status akun wajib disertai test.
- Unit test kalkulasi denda berada di `tests/Unit/AttendancePenaltyCalculatorTest.php`; gunakan data provider untuk batas tarif.
- E2E memakai database terpisah yang dibuat ulang otomatis oleh `tests/e2e/global-setup.js`; jangan mengarahkan `.env.e2e` ke database lokal/produksi.
- E2E memakai `workers: 1` karena data dibagi antar skenario; gunakan locator yang tahan terhadap elemen tersembunyi (misalnya `#nip`, bukan label yang juga dipakai modal edit).
- Jangan menambahkan `waitForTimeout` sebagai pengganti assertion Playwright.
- Jika test tidak dapat dijalankan, jelaskan alasannya pada respons akhir.

## CI/CD

- Workflow utama: `.github/workflows/ci-cd.yml`. Job: Pint, unit/feature PHP 8.2 & 8.3, build Vite, E2E Playwright, lalu deploy SSH ke VPS pada push/manual dispatch ke `main`.
- Deploy memakai secrets `DEPLOY_HOST`, `DEPLOY_USER`, `DEPLOY_SSH_KEY`, `DEPLOY_PORT` (opsional), dan `DEPLOY_PATH`; variabel `APP_URL` opsional untuk tautan environment.
- Jangan menulis kredensial, host, atau private key di workflow, kode, atau dokumentasi. Jangan menambahkan langkah deploy yang berjalan pada pull request.
- Perubahan pada workflow wajib menjaga job test tetap berjalan tanpa database eksternal dan tanpa akses jaringan khusus selain CDN/Playwright.
- Jangan melakukan commit, push, atau deploy ke produksi tanpa permintaan eksplisit pengguna.

## Checklist Sebelum Menyelesaikan Tugas

- [ ] Perubahan sesuai permintaan dan tidak melebar.
- [ ] `php artisan test` lulus (atau alasan kegagalan dijelaskan).
- [ ] `npm run e2e` lulus bila alur UI/autentikasi berubah (atau alasan kegagalan dijelaskan).
- [ ] `vendor/bin/pint --test` lulus.
- [ ] Workflow CI/CD tetap valid bila diubah.
- [ ] `npm run build` dijalankan bila aset frontend berubah.
- [ ] `CHANGELOG.md` pada bagian `[Unreleased]` sudah diperbarui pada tugas yang sama (atau alasan tidak ada perubahan dijelaskan).
- [ ] Tidak ada secret, file `.env`, atau data sensitif yang ikut berubah.
- [ ] Perubahan implementasi berada di worktree dan branch fitur, bukan di `main`.
- [ ] Tidak ada commit/push ke `main`, force-push, atau merge PR oleh agen.
- [ ] GitHub Issue hanya dibuat setelah pengguna menyetujui isi dan prioritasnya.
