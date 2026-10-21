# Draf GitHub Issue

Dokumen ini berisi 13 draf issue dari [`AUDIT.md`](AUDIT.md). Issue **belum dibuat** di GitHub. Salin judul dan isi ke **Issues → New issue**, lalu tambahkan label yang sesuai. Buat label `high`, `data-integrity`, `reporting`, dan `ops` bila belum tersedia.

Jangan menyertakan data pegawai, kredensial, atau isi `.env` pada issue.

---

## 1. Perbaiki zona waktu dan validasi periode laporan

**Label:** `bug`, `high`
**Temuan:** AUD-01, AUD-04

### Ringkasan

Aplikasi memakai zona waktu UTC dan menerima bulan/tahun laporan tanpa validasi. Absensi di sekitar tengah malam dapat tersimpan pada tanggal yang salah, sedangkan periode tidak valid dapat menghasilkan error 500.

### Bukti

- `config/app.php:68` menetapkan `timezone` ke `UTC`.
- Filter rekap memakai bulan/tahun dari request tanpa validasi pada `AttendanceController`.

### Dampak

- TPP dapat dihitung untuk tanggal atau periode yang salah.
- Pengguna mendapat halaman error, bukan pesan validasi.

### Usulan perbaikan

- [ ] Terapkan `Asia/Makassar` melalui konfigurasi dan `.env.example`.
- [ ] Pastikan tanggal default, dashboard, absensi, dan PDF memakai tanggal lokal.
- [ ] Validasi `bulan` 1–12 dan `tahun` pada rentang yang disepakati.
- [ ] Tambahkan test batas pukul 00.00/23.59 dan periode tidak valid.

### Kriteria selesai

- [ ] Test tanggal lokal dan periode tidak valid lulus.
- [ ] Tidak ada error 500 untuk bulan/tahun tidak valid.
- [ ] Dokumentasi environment mencantumkan zona waktu.

---

## 2. Tambahkan constraint unik dan normalisasi NIP

**Label:** `bug`, `data-integrity`
**Temuan:** AUD-02, AUD-03, AUD-19

### Ringkasan

Absensi belum memiliki unique constraint `(employee_id, tanggal)`, dan NIP belum dinormalisasi serta divalidasi unik saat edit dan import. Duplikat dapat membuat rekap dan TPP tidak deterministik.

### Bukti

- Migrasi `attendances` belum memiliki unique index pasangan pegawai/tanggal.
- `updateEmployee` dan import CSV belum memeriksa NIP duplikat.

### Dampak

- Satu pegawai dapat memiliki dua absensi pada tanggal yang sama.
- NIP ganda menyulitkan pencarian, import ulang, dan audit.

### Usulan perbaikan

- [ ] Backup data, deteksi duplikat, dan sepakati strategi pembersihan sebelum migrasi.
- [ ] Tambahkan unique index `(employee_id, tanggal)` dan index pendukung.
- [ ] Normalisasi NIP menjadi digit dan validasi unik saat tambah/edit/import.
- [ ] Tampilkan kesalahan per baris pada import, bukan error 500.
- [ ] Tambahkan test duplikat untuk create, update, import, dan database.

### Kriteria selesai

- [ ] Database menolak absensi ganda.
- [ ] Edit/import NIP duplikat menghasilkan pesan validasi.
- [ ] Migrasi diuji pada salinan data produksi.

---

## 3. Amankan import CSV pegawai

**Label:** `bug`, `validation`
**Temuan:** AUD-15

### Ringkasan

Import CSV belum memiliki batas ukuran, validasi MIME/struktur, dan laporan hasil per baris. File besar atau tidak valid dapat mengganggu proses dan menyisakan data setengah jadi.

### Bukti

- `importEmployees` memproses file tanpa validasi ukuran dan tipe yang memadai.
- Baris tidak valid hanya dilewati tanpa ringkasan yang jelas.

### Dampak

- Risiko kehabisan memori/timeout.
- Pengguna tidak tahu baris mana yang gagal dan apakah data tersimpan sebagian.

### Usulan perbaikan

- [ ] Batasi ukuran file dan validasi ekstensi/MIME.
- [ ] Validasi setiap baris dan kumpulkan kesalahan.
- [ ] Simpan dalam transaksi; jangan tinggalkan hasil parsial.
- [ ] Tampilkan ringkasan berhasil/gagal per baris.
- [ ] Tambahkan test file kosong, besar, duplikat, dan format salah.

### Kriteria selesai

- [ ] Import invalid tidak mengubah data.
- [ ] Pengguna mendapat ringkasan hasil yang dapat ditindaklanjuti.
- [ ] Test import berbahaya lulus.

---

## 4. Hardening produksi: debug, cookie, security header

**Label:** `security`, `high`
**Temuan:** AUD-05, AUD-06

### Ringkasan

Konfigurasi contoh masih mengaktifkan debug, cookie aman belum diwajibkan, dan aplikasi belum mengirim security header atau mengenali proxy HTTPS.

### Bukti

- `.env.example:4` memakai `APP_DEBUG=true`.
- Konfigurasi session belum mewajibkan `secure`/`http_only`/`same_site`.
- `bootstrap/app.php` belum menambahkan security header atau trusted proxy.

### Dampak

- Detail internal dan data sensitif berpotensi terekspos saat produksi salah konfigurasi.
- Risiko clickjacking, MIME sniffing, dan kebocoran referrer.

### Usulan perbaikan

- [ ] Sediakan template environment produksi dengan debug mati dan cookie aman.
- [ ] Tambahkan header `X-Frame-Options`/`frame-ancestors`, `X-Content-Type-Options`, `Referrer-Policy`, dan CSP yang kompatibel dengan aset.
- [ ] Konfigurasikan trusted proxy untuk HTTPS di VPS.
- [ ] Tambahkan feature test header dan konfigurasi produksi.

### Kriteria selesai

- [ ] Smoke test produksi tidak menampilkan debug.
- [ ] Header keamanan terverifikasi.
- [ ] Dokumentasi deploy menjelaskan HTTPS/proxy dan cookie.

---

## 5. Backup, rollback, smoke test, dan audit dependency deploy

**Label:** `ops`, `security`, `high`
**Temuan:** AUD-07, AUD-25

### Ringkasan

Deploy menjalankan migrasi dan build tanpa backup, rollback, health check, atau penguncian deploy. CI juga belum menjalankan audit dependency.

### Bukti

- `.github/workflows/ci-cd.yml` melakukan deploy SSH ke `main` tanpa backup/rollback.
- Tidak ada `composer audit`, `npm audit`, atau Dependabot.

### Dampak

- Migrasi yang gagal dapat merusak data produksi tanpa jalur pemulihan.
- Dua deploy bersamaan dapat saling menimpa.
- Kerentanan dependency tidak terdeteksi.

### Usulan perbaikan

- [ ] Backup database dan file penting sebelum migrasi.
- [ ] Simpan rilis sebelumnya dan dokumentasikan rollback.
- [ ] Tambahkan health check setelah deploy dan kunci deploy (`concurrency`).
- [ ] Jalankan audit dependency dan aktifkan Dependabot.
- [ ] Uji prosedur di staging, bukan produksi.

### Kriteria selesai

- [ ] Deploy staging berhasil, gagal, dan rollback diuji.
- [ ] Backup dapat dipulihkan.
- [ ] Audit dependency berjalan di CI.

---

## 6. Konsistensi laporan web/PDF dan definisi dashboard

**Label:** `bug`, `reporting`
**Temuan:** AUD-08, AUD-09, AUD-22, AUD-23

### Ringkasan

Metrik dashboard dapat menyesatkan saat input belum lengkap, PDF belum memuat cuti/nomor halaman, dan tanggal/lokalisasi belum konsisten. Web dan PDF juga belum diuji memakai data yang sama.

### Bukti

- Perhitungan persentase dashboard memakai basis yang berbeda dari rekap.
- `rekap_pdf.blade.php` belum memuat kolom cuti dan nomor halaman.
- Locale aplikasi masih `en`.

### Dampak

- Pimpinan dapat menerima laporan yang tidak konsisten.
- PDF sulit diverifikasi dan kurang lengkap.

### Usulan perbaikan

- [ ] Tulis definisi metrik dashboard dan perlakukan data belum lengkap secara eksplisit.
- [ ] Pastikan web dan PDF memakai `generateRekap` yang sama.
- [ ] Tambahkan kolom cuti, nomor halaman, dan metadata periode/tanggal cetak.
- [ ] Terapkan locale Indonesia pada validasi dan format tanggal.
- [ ] Tambahkan test isi web/PDF untuk fixture yang sama.

### Kriteria selesai

- [ ] Angka web dan PDF identik untuk periode yang sama.
- [ ] PDF memuat semua status dan nomor halaman.
- [ ] Dashboard tidak menampilkan skor menyesatkan saat data kosong.

---

## 7. Kelola TPP pagu/nominal dan audit trail

**Label:** `feature`, `data-integrity`
**Temuan:** AUD-10, AUD-12

### Ringkasan

Aplikasi menghitung nominal potongan dari `tpp_pagu`, tetapi pagu tidak dapat dikelola dan nominal tidak ditampilkan. Belum ada audit trail untuk perubahan absensi, pegawai, atau role.

### Bukti

- Tidak ada UI/otorisasi untuk mengubah `tpp_pagu`.
- Perubahan data penting tidak mencatat aktor dan nilai lama/baru.

### Dampak

- Nominal TPP tidak dapat dipertanggungjawabkan.
- Sulit menelusuri kesalahan input atau perubahan role.

### Usulan perbaikan

- [ ] Sepakati apakah yang dikelola pagu, nominal, atau keduanya.
- [ ] Tambahkan pengelolaan pagu dengan otorisasi dan validasi.
- [ ] Tampilkan nominal potongan di rekap web/PDF bila disetujui.
- [ ] Catat audit trail perubahan absensi, pegawai, role, dan status akun.
- [ ] Tambahkan test nominal dan audit trail.

### Kriteria selesai

- [ ] Keputusan produk tercatat di dokumentasi.
- [ ] Nominal dapat diverifikasi dari data yang tersimpan.
- [ ] Perubahan penting memiliki jejak aktor dan waktu.

---

## 8. Cabut sesi setelah reset kata sandi

**Label:** `security`
**Temuan:** AUD-13

### Ringkasan

Reset kata sandi oleh admin belum mengakhiri sesi pengguna lain. Sesi yang sudah aktif tetap dapat mengakses aplikasi.

### Bukti

- `Admin\UserController` memperbarui password tanpa invalidasi sesi/remember token.

### Dampak

- Akun yang kredensialnya bocor tetap dapat dipakai setelah reset.

### Usulan perbaikan

- [ ] Invalidasi sesi lain dan rotasi remember token saat reset.
- [ ] Terapkan juga pada perubahan password mandiri bila ada.
- [ ] Tambahkan test sesi lama tidak dapat mengakses route.

### Kriteria selesai

- [ ] Sesi lama berakhir segera setelah reset.
- [ ] Test regresi lulus.

---

## 9. Optimasi index dan query

**Label:** `performance`
**Temuan:** AUD-14

### Ringkasan

Query rekap memakai filter yang tidak mendukung index, dan beberapa relasi berpotensi N+1. Kinerja akan menurun saat data bertambah.

### Bukti

- Filter bulan/tahun memakai fungsi tanggal, bukan rentang.
- Index untuk pencarian rekap terbatas.

### Dampak

- Dashboard dan rekap melambat.
- Beban database meningkat seiring data multi-tahun.

### Usulan perbaikan

- [ ] Ganti filter ke `whereBetween` dan tambahkan index yang sesuai.
- [ ] Eager load relasi yang dipakai di daftar/rekap.
- [ ] Ukur query plan dan waktu respons sebelum/sesudah dengan data realistis.
- [ ] Tambahkan test performa ringan atau benchmark terdokumentasi.

### Kriteria selesai

- [ ] Query plan memakai index.
- [ ] Tidak ada N+1 pada halaman utama.
- [ ] Benchmark terdokumentasi.

---

## 10. Satukan kalkulasi preview dan server

**Label:** `bug`, `frontend`
**Temuan:** AUD-11

### Ringkasan

Kalkulasi denda untuk pratinjau di browser diduplikasi dari service server. Perubahan tarif dapat membuat pratinjau berbeda dari nilai yang disimpan.

### Bukti

- `resources/views/attendance/create.blade.php` menduplikasi tarif dan jam kerja.

### Dampak

- Pengguna melihat nominal berbeda dari hasil simpan.
- Perubahan aturan berisiko tidak konsisten.

### Usulan perbaikan

- [ ] Jadikan server satu-satunya sumber perhitungan, atau bagikan kontrak aturan yang sama.
- [ ] Hapus duplikasi tarif di view.
- [ ] Tambahkan test preview untuk semua batas tarif dan hari Jumat.

### Kriteria selesai

- [ ] Preview sama dengan hasil simpan untuk semua kasus uji.
- [ ] Tarif hanya didefinisikan di satu tempat.

---

## 11. Kelola aset frontend dan CDN

**Label:** `security`, `frontend`
**Temuan:** AUD-16

### Ringkasan

Halaman aktif memuat Bootstrap dan ikon dari CDN tanpa SRI, sementara scaffolding Vite/Tailwind tidak dipakai. Ini menyulitkan kontrol aset dan keamanan supply chain.

### Bukti

- `resources/views/layouts/app.blade.php` memuat aset CDN.
- Build Vite tersedia tetapi halaman tidak memakainya.

### Dampak

- Ketergantungan pada CDN pihak ketiga.
- Aset build tidak terpakai dan membingungkan kontributor.

### Usulan perbaikan

- [ ] Pilih: vendorkan aset lewat build Vite atau gunakan CDN dengan SRI dan versi terkunci.
- [ ] Hapus scaffolding yang tidak dipakai bila tidak diperlukan.
- [ ] Pastikan CSP kompatibel dengan aset terpilih.
- [ ] Tambahkan smoke test halaman tanpa akses CDN bila aset di-vendor.

### Kriteria selesai

- [ ] Aset aktif memiliki strategi keamanan yang jelas.
- [ ] Tidak ada build/scaffolding mati.
- [ ] Halaman tetap tampil benar.

---

## 12. Tambahkan test alur berisiko tinggi

**Label:** `test`
**Temuan:** AUD-17

### Ringkasan

Test belum mencakup duplikat NIP, periode tidak valid, batas zona waktu, constraint absensi, konsistensi PDF, dan pencabutan sesi. E2E hanya berjalan di Chromium desktop.

### Bukti

- Daftar gap terdapat pada AUD-17.

### Dampak

- Regresi pada TPP dan integritas data dapat lolos ke produksi.

### Usulan perbaikan

- [ ] Tambahkan feature test untuk setiap temuan Tinggi.
- [ ] Tambahkan test kontrak preview/server.
- [ ] Tambahkan proyek Playwright mobile.
- [ ] Pertimbangkan ambang coverage untuk service/controller kritis.

### Kriteria selesai

- [ ] Semua temuan Tinggi memiliki test regresi.
- [ ] E2E mobile berjalan di CI.
- [ ] Test stabil tanpa `waitForTimeout`.

---

## 13. Bersihkan dead code, seeder, dan selaraskan dokumentasi

**Label:** `chore`, `docs`
**Temuan:** AUD-18, AUD-20, AUD-21, AUD-24, AUD-26

### Ringkasan

Masih ada view/route/model mati, seeder yang memanggil `truncate` tanpa guard, kontrol pencarian yang kurang aksesibel, deskripsi frontend `AGENTS.md` yang tidak sesuai, dan branch protection belum didokumentasikan.

### Bukti

- `batch.blade.php` mereferensikan route yang tidak ada.
- Seeder memanggil `truncate` tanpa guard environment.
- `AGENTS.md` menyebut Tailwind, halaman memakai Bootstrap.
- Branch protection hanya bergantung pada aturan tertulis.

### Dampak

- Kebingungan kontributor dan risiko kehilangan data saat seeder salah dijalankan.
- Aksesibilitas dan pengamanan repository belum optimal.

### Usulan perbaikan

- [ ] Hapus/implementasikan view, route, dan model mati.
- [ ] Tambahkan guard environment pada seeder.
- [ ] Perbaiki event pencarian, tombol bersihkan, dan `aria-live`.
- [ ] Selaraskan stack frontend di `AGENTS.md`.
- [ ] Dokumentasikan branch protection dan required checks `main`.

### Kriteria selesai

- [ ] Tidak ada referensi route/view mati.
- [ ] Seeder menolak environment non-lokal.
- [ ] Pencarian dapat dioperasikan dengan keyboard.
- [ ] Dokumentasi stack konsisten.
- [ ] Branch protection terdokumentasi.
