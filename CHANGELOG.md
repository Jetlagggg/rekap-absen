
Semua perubahan penting pada proyek **Rekap Absen** dicatat di file ini.

Format mengikuti [Keep a Changelog](https://keepachangelog.com/id/1.1.0/), dan penomoran versi menggunakan [Semantic Versioning](https://semver.org/lang/id/).

> Catatan: proyek ini belum memiliki riwayat Git. Entri untuk versi 1.0.0 sampai 1.1.1 direkonstruksi dari snapshot `rekap-absen.zip` (17 Februari 2026), `rekap-absen (2).zip` (9 April 2026), serta tanggal modifikasi berkas. Nomor versi disusun untuk keperluan dokumentasi.

## [Unreleased]

### Ditambahkan

- `AGENTS.md` berisi panduan kerja agen AI dan kontributor: perintah penting, struktur kode, aturan domain absensi/TPP, testing, keamanan, dan kewajiban mencatat perubahan di changelog.
- Test fitur untuk angka dashboard, validasi penyimpanan absensi massal, konsistensi layout, dan laporan tanpa data.
- Layout bersama `layouts/app.blade.php` untuk dashboard, input absensi, laporan TPP, dan kelola pegawai, lengkap dengan navigasi aktif, skip link, ringkasan error, dan footer.
- Kolom Cuti pada tabel laporan kinerja agar semua status kehadiran terlihat.
- Halaman login dan logout dengan tampilan yang selaras dengan app shell.
- Perintah `php artisan app:create-admin` untuk membuat akun administrator atau mengatur ulang kata sandinya tanpa menyimpan kata sandi di kode; kata sandi minimal 12 karakter.
- Test autentikasi: proteksi route, login valid/tidak valid, rate limit, logout, dan pembuatan akun admin.
- Panel admin `/admin/users` untuk melihat, menambah, mengubah role/status, dan mereset kata sandi pengguna; halaman daftar dan edit responsif.
- Migrasi kolom `role` dan `is_active` pada tabel `users`; pengguna lama otomatis menjadi admin aktif.
- Middleware `admin` dan `active`, tautan navigasi Pengguna khusus admin, serta test panel admin untuk akses role, tambah/ubah/nonaktifkan pengguna, reset kata sandi, dan akun nonaktif.

### Diubah

- Penyimpanan absensi massal kini menolak baris berstatus hadir tanpa jam masuk, menampilkan ringkasan error, dan mempertahankan isian pengguna.
- Pratinjau potongan pada halaman input disamakan dengan aturan server: izin 1,75%, terlambat bertingkat, pulang cepat 1%, dan lupa absen pulang 1,5%.
- Status Dinas Luar disimpan sebagai `dinas luar`; nilai lama `dinas` tetap diterima.
- Label dan keterangan kartu dashboard diperjelas agar sesuai dengan data yang dihitung.
- Deklarasi font Inter yang tidak pernah dimuat dihapus dari halaman input; tipografi mengikuti Bootstrap.
- Seluruh halaman aktif disatukan ke satu bahasa visual "register pemerintah": header navy, garis emas, panel bergaris tipis, tipografi Georgia untuk judul, dan aksen warna status yang konsisten.
- Dashboard, input absensi, laporan, dan kelola pegawai kini memakai judul, aksi, tabel, serta jarak yang seragam; navigasi menandai halaman aktif.
- Tabel laporan diberi legenda kolom dan keterangan bahwa hadir mencakup dinas luar.
- Halaman kelola pegawai memakai tata letak dua kolom, tombol aksi berlabel aksesibel, dan pencarian nama/NIP/jabatan.
- Navigasi utama menampilkan tombol Keluar; halaman login menyembunyikan navigasi aplikasi.
- `AGENTS.md` mencantumkan perintah pembuatan akun admin, aturan perlindungan route, dan kewajiban `actingAs` pada test fitur.
- `AGENTS.md` dipertegas: setiap perubahan wajib dicatat di `CHANGELOG.md` pada tugas yang sama, termasuk perubahan dokumentasi; tugas belum selesai tanpa pembaruan changelog.
- `app:create-admin` kini menetapkan role admin dan status aktif saat membuat atau mengatur ulang akun.
- Login menolak akun nonaktif; sesi akun yang dinonaktifkan dihentikan oleh middleware `active`.
- `AGENTS.md` mencantumkan struktur controller/model/view admin, aturan role/status akun, dan kewajiban test untuk perubahan role.
- Pint merapikan format beberapa file lama (model, migrasi, seeder) tanpa perubahan perilaku.
- `README.md` diganti dari template Laravel menjadi dokumentasi proyek: fitur, role, instalasi, perintah penting, aturan perhitungan TPP, struktur, testing, deployment, dan keamanan.
- `README.md` mencantumkan URL clone repositori GitHub `https://github.com/Jetlagggg/rekap-absen.git`.

### Diperbaiki

- Angka "Hadir Hari Ini" dan "Telat Hari Ini" pada dashboard kini hanya menghitung absensi berstatus hadir; "Rata-Rata Disiplin" tidak lagi menghitung sakit, cuti, atau dinas sebagai tepat waktu.
- Jam pulang tidak lagi diambil dari kondisi jam masuk saat menampilkan data absensi.
- Parameter tanggal pada halaman input divalidasi agar nilai tidak valid tidak merusak tampilan.
- Laporan tidak lagi menampilkan skor disiplin 100% untuk periode tanpa absensi; nilai ditampilkan sebagai tanda hubung disertai keterangan.
- Konfirmasi hapus pegawai tidak lagi gagal ketika nama mengandung apostrof.
- Error validasi pada halaman kelola pegawai kini terlihat, baik ringkasan maupun pesan di dekat kolom.
- Halaman kelola pegawai tidak lagi menyebabkan scroll horizontal pada layar ponsel; tabel bergulir di dalam panelnya.
- Navigasi mobile admin tidak lagi memotong menu Pengguna dan Keluar; item navigasi dibungkus agar tetap terlihat.

### Keamanan

- Semua route aplikasi kini memerlukan login; tamu diarahkan ke halaman login.
- Login dibatasi lima percobaan per menit per kombinasi email dan IP, memakai pesan error generik agar tidak membocorkan status akun.
- Sesi diperbarui setelah login dan dihapus saat logout; kata sandi disimpan sebagai hash melalui cast model `User`.
- Logout hanya dapat dilakukan melalui `POST` yang dilindungi CSRF, bukan tautan `GET`.
- Tidak ada registrasi publik; akun hanya dibuat oleh administrator melalui perintah artisan atau panel admin.
- Panel pengguna hanya dapat diakses role admin; operator yang mencoba membuka `/admin/users` mendapat respons 403.
- Reset kata sandi minimal 12 karakter, wajib dikonfirmasi, tidak menampilkan kata sandi lama, dan mencabut remember token pengguna.
- Admin tidak dapat menurunkan role atau menonaktifkan akun sendiri; minimal satu administrator aktif harus tersedia.
- Akun nonaktif tidak dapat login dan sesi aktifnya dihentikan oleh middleware `active`.
- `.gitignore` mengabaikan `database/*.sqlite`, file jurnalnya, serta artefak tooling `.impeccable` dan `.qodo` agar database lokal, data pegawai, dan hash kata sandi tidak ikut ter-push.
- `DatabaseSeeder` tidak lagi membuat akun uji `test@example.com` dengan kata sandi default; akun dibuat melalui `php artisan app:create-admin` atau panel admin.

## [1.1.1] - 2026-05-31

### Diubah

- Membersihkan komentar usang pada `AttendanceController`, model `Attendance`, dan migrasi `employees`. Tidak ada perubahan perilaku aplikasi.

## [1.1.0] - 2026-02-26

### Ditambahkan

- Fitur edit data pegawai melalui modal pada halaman kelola pegawai, dengan endpoint `PUT /employee/{id}`.
- Import data pegawai dari file CSV dengan format `nip,nama,jabatan`. Baris header dan baris dengan kolom kurang dari tiga dilewati.
- Format otomatis NIP/NIPPPK menjadi kelompok 8-6-1-3 digit saat diketik, dengan batas 21 karakter.
- Aset logo Kabupaten Kutai Barat (`public/img/kubar.png`) dan Dinas PUPR (`public/img/pupr.png`).
- Favicon logo PUPR pada seluruh halaman web.
- Logo instansi pada navbar dashboard dan halaman input absensi.
- Kop laporan PDF dengan logo Kutai Barat dan PUPR (disematkan sebagai base64), alamat kantor, dan kota Sendawar.
- Relasi `Employee::attendances()` dan konstanta status kehadiran pada model `Attendance`.
- Validasi NIP wajib dan unik saat menambah pegawai.
- Nomor urut pada tabel data pegawai.

### Diubah

- Penyimpanan absensi massal dibungkus `DB::transaction` agar tersimpan secara atomik.
- Perhitungan rekap bulanan dipusatkan pada helper `generateRekap()` dan memakai eager loading relasi `attendances`.
- Urutan pegawai pada halaman input absensi dan kelola pegawai diseragamkan berdasarkan `created_at` menaik. Nama dan jabatan ditampilkan dalam huruf kapital.
- Tampilan halaman input absensi didesain ulang: top bar gelap, kartu membulat, status memakai `form-select`, badge status, dan tombol simpan sticky.
- Judul dashboard diubah menjadi "Dashboard Rekap Absen" dan navbar menampilkan logo serta nama instansi.
- Input NIP diubah dari tipe `number` menjadi `text` agar dapat menerima NIPPPK dan spasi.
- Kop PDF memakai tabel agar logo kiri, teks tengah, dan logo kanan sejajar.
- Judul kolom PDF "JML HADIR" diubah menjadi "JUMLAH HADIR".
- Konfirmasi hapus pegawai disederhanakan.
- Import `TppRule` yang tidak terpakai dihapus dari controller.

### Diperbaiki

- Rekap web dan PDF kini memakai sumber perhitungan yang sama sehingga angkanya konsisten.
- NIP pejabat penandatangan pada PDF dikoreksi menjadi `19921006 202012 1 001`.

### Dihapus

- Tombol "Default Hadir (07:30 - 16:00)" dari halaman input absensi.
- Teks keterangan "Persentase pegawai yang datang sebelum 07:30" dari dashboard.

## [1.0.0] - 2026-02-10

### Ditambahkan

- Aplikasi Laravel 12 / PHP 8.2 untuk rekap absensi dan perhitungan TPP.
- Skema database `employees`, `attendances`, dan `tpp_rules`, termasuk relasi cascade dari absensi ke pegawai.
- Dashboard berisi total pegawai, jumlah absen hari ini, jumlah telat hari ini, persentase kehadiran, dan persentase disiplin bulan berjalan.
- Input absensi massal per tanggal dengan status hadir, sakit, izin, cuti, dinas luar, dan alpha.
- Perhitungan potongan otomatis:
  - Toleransi jam masuk sampai 08:10.
  - Terlambat 1-30 menit: 0,5%; 31-60 menit: 1%; 61-90 menit: 1,25%; lebih dari 90 menit: 1,5%.
  - Pulang sebelum waktunya: 1%, dengan jam pulang standar 11:00 pada hari Jumat dan 16:00 pada hari lain.
  - Lupa absen pulang: 1,5%.
  - Alpha: 3%; izin: 1,75%; sakit, cuti, dan dinas luar: 0%.
- Rekap kinerja bulanan di web dengan filter bulan dan tahun (2025-2099), menampilkan jumlah hadir (termasuk dinas luar), telat, sakit, izin, alpha, dinas luar, total potongan, dan skor kinerja.
- Unduh laporan PDF A4 landscape menggunakan `barryvdh/laravel-dompdf`.
- Kelola pegawai: tambah, hapus, dan cari.
- Seeder contoh pegawai dan aturan TPP.
- Accessor nominal potongan berdasarkan 40% dari `tpp_pagu`.
- Model `Employee`, `Attendance`, dan `TppRule`.
