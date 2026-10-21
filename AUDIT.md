# Audit Rekap Absen

Tanggal audit: 21 Oktober 2026

## Ruang Lingkup dan Metode

Audit ini meninjau kode aplikasi Laravel, skema database, tampilan Blade, konfigurasi, test, dan workflow CI/CD. Fokusnya adalah keamanan, hardening, logika bisnis, integritas data, efisiensi, efektivitas, serta kesiapan operasional.

Metode yang digunakan adalah review statis dan penelusuran alur kode. Audit ini bukan penetration test, bukan uji beban, dan tidak mengakses data pegawai produksi. Nomor baris mengacu pada kondisi repository saat audit.

## Ringkasan

- 7 temuan **Tinggi**: berpotensi membuat data absensi/TPP salah, error 500 pada alur utama, atau memperlemah keamanan produksi.
- 10 temuan **Sedang**: risiko nyata dengan dampak terbatas atau membutuhkan kondisi tertentu.
- 9 temuan **Rendah**: maintainability, konsistensi dokumentasi, dan pengalaman pengguna.
- Tidak ditemukan celah yang langsung dapat dieksploitasi tanpa kredensial, tetapi integritas data TPP dan keamanan deployment perlu diprioritaskan.

### Definisi Prioritas

| Prioritas | Arti |
| --- | --- |
| Tinggi | Dapat menyebabkan data TPP/absensi salah atau hilang, error pada alur utama, atau eksposur produksi. Perlu segera diperbaiki. |
| Sedang | Risiko nyata, tetapi dampaknya terbatas atau memerlukan kondisi tertentu. Masuk roadmap jangka pendek. |
| Rendah | Tidak mengancam operasi secara langsung; fokus pada kualitas, konsistensi, dan kemudahan perawatan. |

## 1. Keamanan dan Autentikasi

### AUD-01 — Zona waktu aplikasi menggunakan UTC [Tinggi]

- **Bukti:** `config/app.php:68` mengunci `timezone` ke `UTC`; `.env.example` tidak menyediakan `APP_TIMEZONE`. `now()` dipakai untuk tanggal default dan dashboard di `app/Http/Controllers/AttendanceController.php:18-19`, serta tahun footer di `resources/views/layouts/app.blade.php:522`.
- **Dampak:** Kutai Barat memakai WITA (UTC+8). Antara pukul 00.00–07.59 WITA, aplikasi masih menganggap tanggal kemarin. Absensi dapat tersimpan pada tanggal yang salah, dashboard "hari ini" kosong, dan tanggal tanda tangan PDF keliru.
- **Rekomendasi:** Tambahkan `APP_TIMEZONE=Asia/Makassar` dan ubah konfigurasi menjadi `env('APP_TIMEZONE', 'Asia/Makassar')`. Tambahkan test batas hari dengan waktu dibekukan.

### AUD-05 — Default konfigurasi produksi belum aman [Tinggi]

- **Bukti:** `.env.example:4` memakai `APP_DEBUG=true`; panduan deployment di `README.md:277-283` hanya menetapkan `APP_ENV`, `APP_DEBUG`, dan `APP_URL`. `config/session.php:172` membaca `SESSION_SECURE_COOKIE` tanpa nilai aman default, dan `SESSION_ENCRYPT` masih `false` pada `.env.example:32`.
- **Dampak:** Jika `.env.example` disalin tanpa penyesuaian, stack trace dan konfigurasi internal dapat terlihat saat error. Cookie sesi juga dapat terkirim melalui HTTP bila server tidak memaksa HTTPS.
- **Rekomendasi:** Buat template `.env.production.example` dengan `APP_ENV=production`, `APP_DEBUG=false`, `APP_TIMEZONE=Asia/Makassar`, `SESSION_SECURE_COOKIE=true`, `SESSION_SAME_SITE=lax`, dan `APP_LOCALE=id`. Tambahkan pemeriksaan startup atau dokumentasi deploy yang mewajibkan nilai tersebut.

### AUD-06 — Belum ada security header dan konfigurasi reverse proxy [Tinggi]

- **Bukti:** `bootstrap/app.php:15-20` hanya mendaftarkan alias middleware; tidak ada middleware header keamanan. Tidak ada konfigurasi trusted proxies. Layout memuat script dan stylesheet dari CDN di `resources/views/layouts/app.blade.php:8-9` dan `:525`.
- **Dampak:** Aplikasi rentan terhadap clickjacking, MIME sniffing, dan kebocoran referrer. Jika berada di balik reverse proxy HTTPS, Laravel juga dapat salah mendeteksi skema URL.
- **Rekomendasi:** Tambahkan middleware untuk `Content-Security-Policy`, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, dan HSTS saat HTTPS. Konfigurasikan `TrustProxies` sesuai topologi server.

### AUD-13 — Reset kata sandi tidak mengakhiri sesi lain [Sedang]

- **Bukti:** `app/Http/Controllers/Admin/UserController.php:86-89` hanya menghapus `remember_token`. Tidak ada penghapusan sesi pengguna atau mekanisme session version.
- **Dampak:** Sesi yang sudah aktif tetap dapat memakai akun setelah kata sandi direset, sehingga reset tidak langsung mencabut akses pada perangkat lain.
- **Rekomendasi:** Hapus sesi pengguna pada driver database, gunakan session version, atau panggil mekanisme logout perangkat lain. Tambahkan test bahwa sesi lama tidak lagi valid.

### AUD-16 — Aset frontend dari CDN tanpa SRI dan build Vite tidak dipakai [Sedang]

- **Bukti:** `resources/views/layouts/app.blade.php:8-9,525` memuat Bootstrap, Bootstrap Icons, dan JavaScript dari jsDelivr tanpa `integrity`. `resources/views/attendance/create.blade.php:243-247` juga memuat Flatpickr dari CDN. Sebaliknya, `@vite` hanya dipakai di `resources/views/welcome.blade.php:15`, halaman yang tidak memiliki route.
- **Dampak:** Aplikasi bergantung pada internet dan ketersediaan CDN. Jika CDN terganggu, tampilan dan date picker rusak. Tanpa SRI, kompromi pada CDN dapat menyuntikkan script ke halaman yang sudah terautentikasi. Build Vite saat ini tidak memberi manfaat pada halaman aplikasi.
- **Rekomendasi:** Pilih satu strategi: vendor aset melalui npm dan `@vite`, atau tetap memakai CDN dengan versi terkunci dan SRI. Hapus build yang tidak terpakai bila aset tetap CDN.

## 2. Otorisasi dan Akun

### AUD-07 — Deploy produksi tanpa backup, rollback, dan smoke test [Tinggi]

- **Bukti:** `.github/workflows/ci-cd.yml:170-187` menjalankan `git reset --hard`, `migrate --force`, lalu build. Tidak ada backup database, release sebelumnya, health check, atau rollback. `concurrency` di `:9-11` hanya membatalkan job PR, bukan deploy `main`.
- **Dampak:** Migrasi yang gagal atau build yang rusak dapat meninggalkan aplikasi dalam keadaan tidak konsisten. Perubahan data tidak dapat dipulihkan tanpa backup. Dua push berdekatan dapat memicu deploy yang saling menimpa.
- **Rekomendasi:** Backup database sebelum migrasi, gunakan release directory/symlink atau tag rollback, jalankan health check setelah deploy, dan sediakan langkah rollback otomatis. Serialkan deploy `main`.

### AUD-12 — Tidak ada audit trail untuk perubahan data TPP dan absensi [Sedang]

- **Bukti:** Tabel `attendances` dan `employees` tidak memiliki `created_by`, `updated_by`, atau log perubahan. Semua operator dapat mengubah absensi dan data pegawai; tidak ada riwayat siapa mengubah apa.
- **Dampak:** Sengketa TPP sulit diselesaikan karena perubahan nominal tidak dapat ditelusuri. Kesalahan input atau perubahan yang tidak sah tidak dapat diaudit.
- **Rekomendasi:** Tambahkan activity log/audit table yang mencatat aktor, waktu, aksi, dan nilai sebelum/sesudah untuk absensi, pegawai, dan perubahan role. Sediakan tampilan riwayat read-only.

### AUD-18 — Jalur pegawai dan admin memiliki perilaku error yang tidak konsisten [Rendah]

- **Bukti:** `app/Http/Controllers/AttendanceController.php:243-247` memakai `Employee::destroy($id)` tanpa `findOrFail`, sehingga ID yang tidak ada tetap menghasilkan pesan sukses. `updateEmployee` di `:310-327` tidak memakai route model binding. `app/Http/Controllers/Admin/UserController.php` sudah memakai route model binding.
- **Dampak:** Pengguna mendapat umpan balik palsu saat menghapus data yang sudah tidak ada, dan pola penanganan data tidak konsisten.
- **Rekomendasi:** Gunakan route model binding atau `findOrFail` pada semua aksi edit/hapus, lalu kembalikan 404 yang sesuai.

## 3. Integritas Data dan Validasi

### AUD-02 — Tidak ada constraint unik absensi per pegawai dan tanggal [Tinggi]

- **Bukti:** `database/migrations/2026_02_03_123414_create_attendances_table.php:14-34` tidak memiliki unique index pada `(employee_id, tanggal)`. Aplikasi mengandalkan `updateOrCreate` di `app/Http/Controllers/AttendanceController.php:198-211`.
- **Dampak:** Dua request bersamaan dapat membuat dua baris untuk pegawai dan tanggal yang sama. Rekap akan menghitung potongan dua kali dan laporan menjadi tidak valid.
- **Rekomendasi:** Buat migrasi yang membersihkan duplikat (simpan baris terbaru), lalu tambahkan unique index `(employee_id, tanggal)`. Tangani pelanggaran constraint dengan pesan validasi yang jelas.

### AUD-03 — NIP hanya unik saat tambah, tidak saat edit/import [Tinggi]

- **Bukti:** `storeEmployee` memakai `unique:employees,nip` di `app/Http/Controllers/AttendanceController.php:227-231`, tetapi `updateEmployee` hanya `required` di `:312-316`. `importEmployee` memakai `Employee::insert` tanpa validasi di `:337-362`. Kolom `nip` tetap memiliki unique index di `database/migrations/2026_02_03_123346_create_employees_table.php:16`.
- **Dampak:** Edit atau import dengan NIP duplikat memicu `QueryException`/HTTP 500, bukan pesan validasi. NIP yang sama dengan format spasi berbeda juga dapat lolos sebagai data ganda.
- **Rekomendasi:** Gunakan `Rule::unique('employees', 'nip')->ignore($employee->id)` pada update. Normalisasi NIP menjadi digit sebelum validasi/penyimpanan. Validasi setiap baris CSV dan laporkan baris yang gagal, bukan menggagalkan seluruh import tanpa penjelasan.

### AUD-04 — Filter bulan dan tahun laporan tidak divalidasi [Tinggi]

- **Bukti:** `rekapBulanan` dan `downloadPdf` mengambil `bulan`/`tahun` langsung dari request di `app/Http/Controllers/AttendanceController.php:250-270`. Nilai tersebut dipakai pada `whereMonth`/`whereYear`, `Carbon::create` di `resources/views/attendance/rekap.blade.php:6`, `mktime` di `resources/views/attendance/rekap_pdf.blade.php:78`, dan nama file PDF.
- **Dampak:** Input array, string non-numerik, bulan 13, atau tahun di luar rentang dapat menghasilkan error 500, laporan periode yang salah, atau nama file yang tidak aman.
- **Rekomendasi:** Validasi dengan `integer`, `between:1,12`, dan rentang tahun yang didukung. Cast ke integer sebelum digunakan. Tambahkan feature test untuk input tidak valid pada halaman web dan PDF.

### AUD-15 — Import CSV belum aman dan belum tervalidasi [Sedang]

- **Bukti:** `app/Http/Controllers/AttendanceController.php:329-366` memuat seluruh file dengan `file()`/`str_getcsv`, selalu membuang baris pertama, tidak membatasi ukuran/jumlah baris, tidak memvalidasi NIP/nama/jabatan, dan memakai `insert` massal. Duplikat NIP akan memicu error database.
- **Dampak:** File besar dapat menghabiskan memori. Baris kosong atau tidak valid dapat masuk ke database. Pengguna tidak mendapat laporan baris mana yang gagal.
- **Rekomendasi:** Baca file secara streaming dengan `fgetcsv`, batasi ukuran dan jumlah baris, validasi tiap baris, normalisasi NIP, deteksi duplikat di file dan database, gunakan transaksi, serta tampilkan ringkasan berhasil/gagal per baris.

### AUD-19 — Validasi panjang dan format data pegawai belum konsisten [Rendah]

- **Bukti:** `storeEmployee` dan `updateEmployee` tidak membatasi panjang `nip`, `nama`, atau `jabatan` di `app/Http/Controllers/AttendanceController.php:225-327`. Form tambah membatasi NIP 21 karakter melalui HTML, tetapi server tidak. NIP disimpan dengan spasi dari form, sedangkan import menyimpan nilai mentah.
- **Dampak:** Pada MySQL/PostgreSQL, input melebihi batas kolom dapat terpotong atau error. Format NIP yang berbeda membuat pencarian dan pengecekan unik tidak konsisten.
- **Rekomendasi:** Tambahkan aturan `string|max` di server, normalisasi NIP, dan gunakan FormRequest agar aturan tambah/edit/import konsisten.

### AUD-20 — Seeder dapat menghapus data tanpa pengaman environment [Rendah]

- **Bukti:** `database/seeders/EmployeeSeeder.php:13` dan `database/seeders/TppRuleSeeder.php:13` memanggil `truncate()` tanpa pemeriksaan environment. `E2eSeeder` sudah memiliki pengaman di `database/seeders/E2eSeeder.php:14-16`.
- **Dampak:** Menjalankan seeder secara tidak sengaja di produksi dapat menghapus seluruh pegawai dan absensi terkait.
- **Rekomendasi:** Tolak eksekusi di luar `local`/`testing`, atau ganti dengan `upsert` yang tidak menghapus data. Dokumentasikan risiko ini.

## 4. Logika Absensi, TPP, dan Laporan

### AUD-08 — Formula kehadiran dashboard bergantung pada data yang sudah diinput [Sedang]

- **Bukti:** `app/Http/Controllers/AttendanceController.php:33-47` menghitung hari kerja dari tanggal yang memiliki absensi, lalu mengalikannya dengan total pegawai. Jika belum semua pegawai diinput, penyebut tetap dianggap satu hari penuh. Nilai `?: 1` dipakai saat tidak ada hari kerja.
- **Dampak:** Persentase kehadiran dapat terlihat tinggi atau rendah karena input belum lengkap, bukan karena kehadiran sebenarnya. Angka ini berpotensi disalahartikan dalam evaluasi TPP.
- **Rekomendasi:** Tetapkan definisi bisnis yang eksplisit: kalender hari kerja, jumlah pegawai aktif, atau periode input. Tampilkan indikator kelengkapan data. Sesuaikan test `AttendanceHardenTest` dengan definisi baru.

### AUD-09 — Laporan PDF tidak konsisten dengan laporan web [Sedang]

- **Bukti:** Tabel web menampilkan kolom Cuti di `resources/views/attendance/rekap.blade.php:184-195`, sedangkan PDF hanya memiliki lima kolom rincian di `resources/views/attendance/rekap_pdf.blade.php:88-99`. PDF juga memakai `date('F Y', ...)` yang berbahasa Inggris dan zona waktu server di `:78`, serta `date('d F Y')` di `:140`.
- **Dampak:** Cetak PDF tidak memuat informasi cuti yang terlihat di web. Nama bulan dan tanggal dapat berbeda dari tampilan aplikasi, terutama di akhir bulan UTC.
- **Rekomendasi:** Gunakan satu presenter/DTO rekap untuk web dan PDF, tambahkan kolom cuti, gunakan locale `id` dan zona waktu aplikasi, serta tambahkan test isi PDF.

### AUD-10 — TPP nominal dan pagu tidak dapat dikelola dari aplikasi [Sedang]

- **Bukti:** `tpp_pagu` diisi `0` saat tambah pegawai (`app/Http/Controllers/AttendanceController.php:237`) dan import (`:355`). Tidak ada field/endpoint untuk mengubahnya. Accessor `getNominalPotonganAttribute` di `app/Models/Attendance.php:41-50` tidak dipakai di view mana pun. Laporan hanya menampilkan persentase.
- **Dampak:** Nominal potongan TPP tidak pernah dihitung untuk pegawai yang ditambahkan melalui UI. Klaim "perhitungan TPP" baru sampai persentase, belum nominal rupiah.
- **Rekomendasi:** Konfirmasi kebutuhan produk. Jika nominal diperlukan, tambahkan pengelolaan `tpp_pagu` dengan otorisasi dan audit trail, lalu tampilkan nominal di rekap/PDF. Jika tidak, hapus accessor dan klaim yang tidak terpakai agar tidak menyesatkan.

### AUD-11 — Kalkulasi potongan diduplikasi di JavaScript [Sedang]

- **Bukti:** `resources/views/attendance/create.blade.php:311-323` menyalin ambang terlambat, tarif, jam pulang, dan aturan lupa absen. Sumber server berada di `app/Services/AttendancePenaltyCalculator.php:38-63`.
- **Dampak:** Perubahan aturan di server dapat membuat pratinjau berbeda dari hasil simpan. Ini melanggar prinsip satu sumber perhitungan yang tercantum di `AGENTS.md`.
- **Rekomendasi:** Sediakan endpoint preview atau kirim konfigurasi tarif dari server. Minimal, tambahkan test kontrak yang membandingkan preview dan hasil server untuk semua batas tarif.

### AUD-22 — Pesan validasi dan format tanggal belum sepenuhnya berbahasa Indonesia [Rendah]

- **Bukti:** `config/app.php:81` memakai `APP_LOCALE=en`; tidak ada direktori `lang`. Pesan bawaan Laravel untuk validasi seperti `unique`, `max`, dan `required` akan tampil dalam bahasa Inggris. PDF memakai nama bulan Inggris di `resources/views/attendance/rekap_pdf.blade.php:78`.
- **Dampak:** Antarmuka yang sebagian besar berbahasa Indonesia menampilkan pesan campuran, membingungkan pengguna dan mengurangi kesan profesional.
- **Rekomendasi:** Publikasikan `lang/id/validation.php`, set `APP_LOCALE=id`, dan gunakan `translatedFormat`/`isoFormat` untuk tanggal.

### AUD-23 — Laporan PDF belum memiliki nomor halaman dan metadata [Rendah]

- **Bukti:** `resources/views/attendance/rekap_pdf.blade.php` tidak memuat nomor halaman, jumlah halaman, atau identitas dokumen.
- **Dampak:** Laporan multi-halaman sulit diverifikasi dan diarsipkan, terutama jika dipisahkan dari email pengantar.
- **Rekomendasi:** Tambahkan footer nomor halaman dan metadata dokumen (periode, waktu cetak, unit) melalui dompdf.

## 5. Kinerja dan Skalabilitas

### AUD-14 — Index database belum mendukung pola query utama [Sedang]

- **Bukti:** Migrasi `attendances` tidak memiliki index selain primary key dan foreign key. Dashboard memakai beberapa query `whereMonth`/`whereYear` di `app/Http/Controllers/AttendanceController.php:24-53`; input massal memakai join per tanggal di `:78-92`; rekap memuat absensi per bulan di `:275-278`.
- **Dampak:** Dengan ribuan absensi, pemindaian tabel berulang membuat dashboard dan input semakin lambat. `whereMonth`/`whereYear` juga menyulitkan optimasi index.
- **Rekomendasi:** Tambahkan unique index `(employee_id, tanggal)` dan index `tanggal`. Ganti filter bulan/tahun dengan rentang `whereBetween` agar index dapat dipakai. Ukur dengan data realistis sebelum dan sesudah.

### AUD-17 — Test belum mencakup alur berisiko tinggi [Sedang]

- **Bukti:** Test saat ini mencakup autentikasi, panel admin, validasi dasar absensi, dan perhitungan denda, tetapi belum mencakup: duplikat NIP saat edit/import, filter bulan/tahun tidak valid, batas hari zona waktu, constraint unik absensi, konsistensi PDF, dan pencabutan sesi setelah reset kata sandi. E2E hanya berjalan pada Chromium desktop.
- **Dampak:** Regresi pada alur TPP dan integritas data dapat lolos ke produksi.
- **Rekomendasi:** Tambahkan feature test untuk setiap temuan Tinggi, test kontrak preview/server, dan proyek Playwright mobile. Pertimbangkan ambang coverage untuk service dan controller kritis.

## 6. Frontend, UX, dan Aksesibilitas

### AUD-24 — Dokumentasi stack frontend tidak konsisten [Rendah]

- **Bukti:** `AGENTS.md:13` menyebut frontend Tailwind CSS 4, sedangkan halaman aktif memakai Bootstrap 5 dari CDN di `resources/views/layouts/app.blade.php:8-9`. README sudah mendokumentasikan Bootstrap dan alur branch/PR, tetapi `AGENTS.md` belum selaras.
- **Dampak:** Kontributor dapat mengikuti teknologi yang salah, terutama agen yang membaca `AGENTS.md` sebagai acuan utama.
- **Rekomendasi:** Selaraskan deskripsi stack frontend di `AGENTS.md` dengan implementasi, lalu pastikan README tetap merujuk aturan worktree, branch, PR, review, dan merge di `AGENTS.md`.

### AUD-21 — Pencarian dan kontrol kecil belum optimal untuk aksesibilitas [Rendah]

- **Bukti:** Pencarian pegawai memakai event `keyup` di `resources/views/attendance/add-employee.blade.php:326` dan `resources/views/attendance/create.blade.php:251`, tanpa tombol bersihkan atau pengumuman jumlah hasil. Beberapa kontrol memakai `onchange` inline di `resources/views/attendance/create.blade.php:13,178` dan `resources/views/attendance/rekap.blade.php:12,21`.
- **Dampak:** Pengguna keyboard/screen reader mendapat umpan balik terbatas, dan event `keyup` tidak selalu menangkap perubahan dari paste/autofill.
- **Rekomendasi:** Gunakan event `input`, tambahkan tombol bersihkan, umumkan hasil melalui `aria-live`, dan pertimbangkan listener JavaScript modular alih-alih inline.

## 7. Operasional dan CI/CD

### AUD-25 — Kontrol supply chain dan dependency belum dijalankan di CI [Rendah]

- **Bukti:** `.github/workflows/ci-cd.yml` tidak menjalankan `composer audit` atau `npm audit`, tidak ada konfigurasi Dependabot, dan action memakai tag mayor (`actions/checkout@v4`, `shivammathur/setup-php@v2`, `appleboy/ssh-action@v1`).
- **Dampak:** Kerentanan dependency dan perubahan action tidak terdeteksi otomatis.
- **Rekomendasi:** Tambahkan job audit dependency, Dependabot untuk Composer/npm/GitHub Actions, dan pin action ke commit SHA untuk produksi.

### AUD-26 — Branch protection dan required checks belum terdokumentasi [Rendah]

- **Bukti:** Alur PR baru didokumentasikan di `AGENTS.md`, tetapi tidak ada dokumentasi konfigurasi GitHub untuk mewajibkan review, status check, atau larangan push langsung ke `main`.
- **Dampak:** Aturan hanya bergantung pada disiplin agen/kontributor, bukan pada pengaman repository.
- **Rekomendasi:** Aktifkan branch protection untuk `main`: required PR review, required status checks (Pint, test, build, E2E), dan larangan force-push/push langsung. Dokumentasikan di README.

## Usulan Pengelompokan GitHub Issue

Issue belum dibuat. Draf siap salin tersedia di [`GITHUB_ISSUES.md`](GITHUB_ISSUES.md); pengelompokan temuan menjadi issue berikut sudah disetujui:

| Usulan issue | Temuan | Label usulan |
| --- | --- | --- |
| Perbaiki zona waktu dan validasi periode laporan | AUD-01, AUD-04 | `bug`, `high` |
| Tambahkan constraint unik dan normalisasi NIP | AUD-02, AUD-03, AUD-19 | `bug`, `data-integrity` |
| Amankan import CSV pegawai | AUD-15 | `bug`, `validation` |
| Hardening produksi: debug, cookie, security header | AUD-05, AUD-06 | `security`, `high` |
| Backup, rollback, dan smoke test deploy | AUD-07, AUD-25 | `ops`, `high` |
| Konsistensi laporan web/PDF dan definisi dashboard | AUD-08, AUD-09, AUD-22, AUD-23 | `bug`, `reporting` |
| Kelola TPP pagu/nominal dan audit trail | AUD-10, AUD-12 | `feature`, `data-integrity` |
| Cabut sesi setelah reset kata sandi | AUD-13 | `security` |
| Optimasi index dan query | AUD-14 | `performance` |
| Satukan kalkulasi preview dan server | AUD-11 | `bug`, `frontend` |
| Kelola aset frontend dan CDN | AUD-16 | `security`, `frontend` |
| Tambahkan test alur berisiko tinggi | AUD-17 | `test` |
| Bersihkan dead code, seeder, dan selaraskan dokumentasi | AUD-18, AUD-20, AUD-24, AUD-21, AUD-26 | `chore`, `docs` |
