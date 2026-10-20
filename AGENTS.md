# AGENTS.md

Panduan kerja untuk agen AI dan kontributor pada proyek **Rekap Absen**.

> **Aturan mutlak:** setiap perubahan wajib dicatat di `CHANGELOG.md` bagian `[Unreleased]` pada tugas yang sama. Tugas belum selesai jika changelog belum diperbarui.

## Tentang Proyek

Aplikasi rekap absensi dan perhitungan TPP (Tambahan Penghasilan Pegawai) untuk Dinas PUPR Kabupaten Kutai Barat.

- **Backend:** Laravel 12, PHP 8.2
- **Database:** SQLite (`database/database.sqlite`), dengan dukungan konfigurasi Laravel lain
- **Frontend:** Blade + Tailwind CSS 4 + Vite 7 (tanpa framework JS SPA)
- **PDF:** `barryvdh/laravel-dompdf`, A4 landscape
- **Testing:** PHPUnit 11

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
| Semua test | `php artisan test` atau `composer test` |
| Test spesifik | `php artisan test --filter=NamaTest` |
| Format kode | `vendor/bin/pint` |
| Bersihkan cache | `php artisan optimize:clear` |

Selalu jalankan test setelah mengubah logika perhitungan, validasi, import, atau route. Jalankan `npm run build` jika mengubah aset frontend.

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
6. Jangan melakukan commit, push, atau membuat PR kecuali diminta secara eksplisit.

## Struktur dan Konvensi Kode

- **Controller:** `app/Http/Controllers/AttendanceController.php` untuk absensi, pegawai, dan rekap/TPP; `app/Http/Controllers/Auth/LoginController.php` untuk autentikasi; `app/Http/Controllers/Admin/UserController.php` untuk panel pengguna. Tambahkan method pada controller terkait; hindari mencampur logika admin ke controller absensi.
- **Model:** `app/Models/{Attendance,Employee,TppRule,User}.php`.
- **View:** `resources/views/attendance/` untuk halaman absensi dan `resources/views/admin/users/` untuk panel pengguna. Halaman memakai layout bersama `layouts/app.blade.php`; tambahkan CSS khusus halaman melalui `@push('styles')`.
- **Route:** `routes/web.php`, gunakan named route. Jangan mengubah URL atau nama route yang sudah ada tanpa kebutuhan yang jelas.
- **Aset:** `resources/css/app.css`, `resources/js/`, dan gambar di `public/img/`.
- **Migrasi:** Jangan mengubah migrasi yang sudah pernah dijalankan. Buat migrasi baru untuk perubahan skema.
- **Gaya:** PSR-12/Pint, indentasi 4 spasi, UTF-8, LF, baris baru di akhir file.
- **Bahasa:** Kolom domain absensi/pegawai memakai bahasa Indonesia (`nip`, `nama`, `jabatan`, `tpp_pagu`, `tanggal`, `status_kehadiran`); kolom autentikasi mengikuti konvensi Laravel (`email`, `password`, `role`, `is_active`). Teks antarmuka memakai bahasa Indonesia.
- **Dependensi:** Jangan menambah library baru tanpa memeriksa `composer.json` / `package.json` dan tanpa persetujuan pengguna.
- **Larangan:** Jangan mengedit isi `vendor/`, `node_modules/`, `.env`, atau `database/database.sqlite` secara manual.

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

- Test berada di `tests/Feature` dan `tests/Unit`, menggunakan PHPUnit (bukan Pest).
- Konfigurasi test memakai SQLite `:memory:`; gunakan `RefreshDatabase` untuk test yang menyentuh database.
- Semua route aplikasi dilindungi `auth` dan `active`; test fitur yang memanggil route aplikasi wajib memakai `actingAs(User::factory()->create())`, kecuali test autentikasi.
- `User::factory()` membuat operator; untuk test route admin, buat pengguna dengan `role => User::ROLE_ADMIN`.
- Setiap perubahan pada perhitungan TPP, validasi absensi, import pegawai, rekap, role, atau status akun wajib disertai test.
- Jika test tidak dapat dijalankan, jelaskan alasannya pada respons akhir.

## Checklist Sebelum Menyelesaikan Tugas

- [ ] Perubahan sesuai permintaan dan tidak melebar.
- [ ] `php artisan test` lulus (atau alasan kegagalan dijelaskan).
- [ ] `npm run build` dijalankan bila aset frontend berubah.
- [ ] `CHANGELOG.md` pada bagian `[Unreleased]` sudah diperbarui pada tugas yang sama (atau alasan tidak ada perubahan dijelaskan).
- [ ] Tidak ada secret, file `.env`, atau data sensitif yang ikut berubah.
- [ ] Tidak ada commit/push tanpa permintaan eksplisit.
