<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    public function run()
    {
        // Hapus data lama biar gak dobel
        DB::table('employees')->truncate();

        $pegawai = [
            [
                'nip' => '198001012010011001',
                'nama' => 'Budi Santoso, S.Kom', // Si Rajin
                'jabatan' => 'Kepala Seksi IT',
                'tpp_pagu' => 5000000, // TPP 5 Juta
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'nip' => '199005052015022002',
                'nama' => 'Siti Aminah, S.E', // Si Sering Telat
                'jabatan' => 'Bendahara Pengeluaran',
                'tpp_pagu' => 4500000, // TPP 4.5 Juta
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'nip' => '199508172019031003',
                'nama' => 'Joko Anwar', // Si Hobi Pulang Cepat
                'jabatan' => 'Staf Umum',
                'tpp_pagu' => 3000000, // TPP 3 Juta
                'created_at' => now(), 'updated_at' => now(),
            ],
        ];

        DB::table('employees')->insert($pegawai);
    }
}
