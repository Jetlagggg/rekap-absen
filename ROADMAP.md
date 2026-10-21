# Roadmap Rekap Absen

Diperbarui: 21 Oktober 2026

Dokumen ini menerjemahkan temuan di [`AUDIT.md`](AUDIT.md) menjadi urutan pekerjaan. Prioritas disusun berdasarkan risiko terhadap integritas data TPP, keamanan produksi, dan beban operasional.

## Cara Menggunakan Roadmap

1. Satu item roadmap dapat menjadi satu GitHub Issue bila disetujui pengelola.
2. Jangan mengerjakan item yang memerlukan keputusan bisnis sebelum keputusan tersebut dicatat.
3. Setiap perubahan wajib mengikuti alur worktree, branch fitur, PR, test, dan `CHANGELOG.md` di `AGENTS.md`.
4. Tandai item selesai hanya setelah test dan verifikasi manual relevan lulus.

## Fase 1 — Stabilkan Data dan Keamanan Dasar

Target: tidak ada lagi data absensi yang tersimpan pada tanggal salah, duplikat, atau gagal tanpa pesan yang jelas.

| Item | Temuan | Hasil yang diharapkan | Ukuran selesai |
| --- | --- | --- | --- |
| Terapkan zona waktu WITA | AUD-01 | Tanggal default, dashboard, dan PDF memakai tanggal lokal Kutai Barat. | `APP_TIMEZONE=Asia/Makassar`; test batas pukul 00.00 dan 23.59. |
| Unique absensi per pegawai/tanggal | AUD-02 | Tidak mungkin ada dua baris absensi untuk pegawai dan tanggal yang sama. | Migrasi pembersihan duplikat + unique index; test percobaan duplikat. |
| Normalisasi dan validasi NIP | AUD-03, AUD-19 | NIP konsisten, unik saat tambah/edit/import, dan kesalahan tampil sebagai validasi. | Test edit/import NIP duplikat; NIP dinormalisasi menjadi digit. |
| Validasi periode laporan | AUD-04 | Bulan/tahun tidak valid ditolak dengan pesan yang jelas, bukan 500. | Test bulan 0, 13, dan tahun di luar rentang untuk web/PDF. |
| Amankan import CSV | AUD-15 | Import memiliki batas ukuran, validasi per baris, dan laporan hasil. | Test file besar, baris kosong, duplikat, dan baris tidak valid. |
| Hardening environment produksi | AUD-05, AUD-06 | Debug mati, cookie aman, security header aktif, dan proxy HTTPS dikenali. | Template `.env.production.example`; smoke test header dan HTTPS. |
| Deploy dengan backup dan rollback | AUD-07 | Migrasi produksi selalu didahului backup dan memiliki jalur rollback. | Backup otomatis, health check, rollback diuji di staging. |

## Fase 2 — Tingkatkan Kepercayaan pada Laporan TPP

Target: angka yang dilihat di web, PDF, dan nominal TPP dapat dipertanggungjawabkan.

| Item | Temuan | Hasil yang diharapkan | Ukuran selesai |
| --- | --- | --- | --- |
| Audit trail perubahan | AUD-12 | Setiap perubahan absensi/pegawai/role mencatat aktor dan nilai lama/baru. | Log diuji untuk tambah, edit, hapus, dan perubahan role. |
| Kelola pagu dan nominal TPP | AUD-10 | Keputusan produk jelas; jika nominal dipakai, pagu dapat dikelola dan nominal tampil. | Field pagu dengan otorisasi/audit; nominal di web dan PDF; test nominal. |
| Samakan laporan web dan PDF | AUD-09 | Keduanya memakai sumber data yang sama dan memuat kolom cuti. | Test isi PDF dan web untuk periode yang sama. |
| Definisikan ulang metrik dashboard | AUD-08 | Persentase kehadiran/disiplin tidak menyesatkan saat input belum lengkap. | Definisi tertulis di README; test skenario data parsial. |
| Cabut sesi setelah reset kata sandi | AUD-13 | Reset kata sandi langsung mengakhiri sesi lain. | Test sesi lama tidak dapat mengakses route. |
| Lokalisasi pesan dan tanggal | AUD-22 | Validasi dan tanggal konsisten berbahasa Indonesia. | `APP_LOCALE=id`; tidak ada pesan validasi Inggris pada alur utama. |

## Fase 3 — Operasional, Performa, dan Kualitas

Target: aplikasi tetap cepat, aman, dan mudah dirawat saat data bertambah.

| Item | Temuan | Hasil yang diharapkan | Ukuran selesai |
| --- | --- | --- | --- |
| Index dan query rekap | AUD-14 | Dashboard/input/rekap memakai index dan rentang tanggal. | Query plan diperiksa; benchmark sebelum/sesudah dengan data realistis. |
| Test alur berisiko tinggi | AUD-17 | Regresi TPP, keamanan, dan integritas data tertangkap otomatis. | Feature test baru; E2E mobile; coverage service/controller kritis. |
| Satukan kalkulasi preview dan server | AUD-11 | Pratinjau selalu sama dengan hasil simpan. | Test kontrak semua batas tarif dan hari Jumat. |
| Kelola aset frontend | AUD-16 | Aset tidak bergantung pada CDN tanpa pengaman atau build yang tidak terpakai. | Aset di-vendor/di-build; atau CDN dengan SRI; smoke test offline. |
| Supply chain dan branch protection | AUD-25, AUD-26 | Dependency dipantau dan `main` terlindungi. | Audit dependency di CI; Dependabot; required checks; larangan push langsung. |
| Konsistensi error data pegawai | AUD-18 | ID yang tidak ada menghasilkan 404, bukan sukses palsu. | Test hapus/edit ID tidak valid. |

## Fase 4 — UX, Dokumentasi, dan Perawatan

Target: pengalaman pengguna dan dokumentasi sesuai dengan aplikasi yang sebenarnya.

| Item | Temuan | Hasil yang diharapkan | Ukuran selesai |
| --- | --- | --- | --- |
| Perbaiki aksesibilitas pencarian | AUD-21 | Pencarian menangkap paste/autofill dan memberi umpan balik. | Event `input`, tombol bersihkan, `aria-live`; uji keyboard. |
| Metadata PDF | AUD-23 | Laporan multi-halaman mudah diverifikasi. | Nomor halaman dan metadata periode/tanggal cetak. |
| Selaraskan dokumentasi | AUD-24 | README/AGENTS menyebut stack dan alur kerja yang benar. | Review dokumentasi; tidak ada kontradiksi Tailwind/Bootstrap/PR. |
| Bersihkan dead code dan seeder | AUD-20 | Tidak ada route/view/model mati atau seeder berbahaya. | Hapus/implementasikan; seeder menolak environment non-lokal. |

## Backlog Jangka Panjang

- Backup otomatis terjadwal dan simulasi restore berkala.
- Autentikasi dua faktor untuk akun admin.
- Antrean untuk pembuatan PDF besar.
- Tampilan riwayat audit untuk admin.
- Pratinjau import CSV sebelum disimpan.
- Role tambahan untuk verifikator/pengesahan TPP.
- API atau aplikasi mobile untuk input lapangan.
- Cache dashboard dan agregasi laporan untuk data multi-tahun.

## Definition of Done

- Perubahan sesuai item roadmap dan tidak melebar.
- Test baru/updated lulus; `php artisan test`, `npm run e2e` (bila UI berubah), dan `vendor/bin/pint --test` lulus.
- `CHANGELOG.md` bagian `[Unreleased]` diperbarui.
- PR dibuat dari branch fitur, direview dan di-merge oleh pengguna.
- Tidak ada data pegawai, kredensial, atau `.env` yang masuk ke repository.

## Status GitHub Issue

Belum ada issue yang dibuat dari audit ini. Pengelompokan issue yang diusulkan tercantum di bagian akhir [`AUDIT.md`](AUDIT.md); pembuatan issue menunggu persetujuan pengelola.
