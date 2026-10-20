<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TppRuleSeeder extends Seeder
{
    public function run()
    {
        // KOSONGKAN TABLE DULU BIAR GAK DOUBLE KALAU DI-RUN ULANG
        DB::table('tpp_rules')->truncate();

        $rules = [
            // --- A. TERLAMBAT MASUK KERJA ---
            [
                'jenis' => 'terlambat',
                'keterangan' => 'Terlambat 1 s/d 30 Menit',
                'min_menit' => 1,
                'max_menit' => 30,
                'potongan' => 0.50,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'jenis' => 'terlambat',
                'keterangan' => 'Terlambat 31 s/d 60 Menit',
                'min_menit' => 31,
                'max_menit' => 60,
                'potongan' => 1.00,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'jenis' => 'terlambat',
                'keterangan' => 'Terlambat 61 s/d 90 Menit',
                'min_menit' => 61,
                'max_menit' => 90,
                'potongan' => 1.25,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'jenis' => 'terlambat',
                'keterangan' => 'Terlambat > 90 Menit / Tidak Absen Masuk',
                'min_menit' => 91,
                'max_menit' => 9999,
                'potongan' => 1.50,
                'created_at' => now(), 'updated_at' => now(),
            ],

            // --- B. PULANG CEPAT / SEBELUM WAKTUNYA ---
            [
                'jenis' => 'pulang_cepat',
                'keterangan' => 'Pulang Cepat 1 s/d 30 Menit',
                'min_menit' => 1,
                'max_menit' => 30,
                'potongan' => 0.50,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'jenis' => 'pulang_cepat',
                'keterangan' => 'Pulang Cepat 31 s/d 60 Menit',
                'min_menit' => 31,
                'max_menit' => 60,
                'potongan' => 1.00,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'jenis' => 'pulang_cepat',
                'keterangan' => 'Pulang Cepat 61 s/d 90 Menit',
                'min_menit' => 61,
                'max_menit' => 90,
                'potongan' => 1.25,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'jenis' => 'pulang_cepat',
                'keterangan' => 'Pulang Cepat > 90 Menit / Lupa Absen Pulang',
                'min_menit' => 91,
                'max_menit' => 9999,
                'potongan' => 1.55, // Sesuai foto poin c.4 (1,55%)
                'created_at' => now(), 'updated_at' => now(),
            ],
        ];

        DB::table('tpp_rules')->insert($rules);
    }
}
