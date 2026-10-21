<?php

namespace Tests\Unit;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceNominalTest extends TestCase
{
    use RefreshDatabase;

    public function test_nominal_penalty_uses_forty_percent_of_the_tpp_pagu(): void
    {
        $employee = Employee::create([
            'nip' => '198001012006041001',
            'nama' => 'Pegawai Uji',
            'jabatan' => 'Staf',
            'tpp_pagu' => 5_000_000,
        ]);

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'tanggal' => '2025-01-06',
            'status_kehadiran' => Attendance::STATUS_ALPHA,
            'total_potongan_persen' => 2.5,
        ]);

        $this->assertEqualsWithDelta(50_000, $attendance->nominal_potongan, 0.001);
    }

    public function test_nominal_penalty_is_zero_when_there_is_no_deduction(): void
    {
        $employee = Employee::create([
            'nip' => '198502022010012002',
            'nama' => 'Pegawai Uji',
            'jabatan' => 'Staf',
            'tpp_pagu' => 8_000_000,
        ]);

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'tanggal' => '2025-01-06',
            'status_kehadiran' => Attendance::STATUS_SAKIT,
            'total_potongan_persen' => 0,
        ]);

        $this->assertEqualsWithDelta(0, $attendance->nominal_potongan, 0.001);
    }
}
