<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class E2eSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('e2e')) {
            throw new RuntimeException('E2eSeeder hanya boleh dijalankan pada environment e2e.');
        }

        User::create([
            'name' => 'Admin E2E',
            'email' => 'admin@e2e.test',
            'password' => 'e2e-admin-password',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Operator E2E',
            'email' => 'operator@e2e.test',
            'password' => 'e2e-operator-password',
            'role' => User::ROLE_OPERATOR,
            'is_active' => true,
        ]);

        Employee::create([
            'nip' => '198001012006041001',
            'nama' => 'Andi Pratama',
            'jabatan' => 'Staf Administrasi',
            'tpp_pagu' => 5_000_000,
        ]);

        Employee::create([
            'nip' => '198502022010012002',
            'nama' => 'Rina Wulandari',
            'jabatan' => 'Analis Kepegawaian',
            'tpp_pagu' => 6_000_000,
        ]);
    }
}
