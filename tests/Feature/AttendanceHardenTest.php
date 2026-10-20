<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceHardenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    private function employee(string $nip, string $nama): Employee
    {
        return Employee::create([
            'nip' => $nip,
            'nama' => $nama,
            'jabatan' => 'Staf',
            'tpp_pagu' => 0,
        ]);
    }

    public function test_dashboard_only_counts_hadir_and_late_hadir_rows(): void
    {
        $today = now()->toDateString();

        $hadirTepatWaktu = $this->employee('1', 'Hadir Tepat Waktu');
        $hadirTelat = $this->employee('2', 'Hadir Telat');
        $alpha = $this->employee('3', 'Alpha');
        $izin = $this->employee('4', 'Izin');
        $sakit = $this->employee('5', 'Sakit');

        Attendance::create([
            'employee_id' => $hadirTepatWaktu->id,
            'tanggal' => $today,
            'status_kehadiran' => Attendance::STATUS_HADIR,
            'jam_masuk' => '07:55:00',
            'jam_pulang' => '16:00:00',
            'terlambat_menit' => 0,
            'pulang_cepat_menit' => 0,
            'total_potongan_persen' => 0,
        ]);

        Attendance::create([
            'employee_id' => $hadirTelat->id,
            'tanggal' => $today,
            'status_kehadiran' => Attendance::STATUS_HADIR,
            'jam_masuk' => '08:15:00',
            'jam_pulang' => '16:00:00',
            'terlambat_menit' => 15,
            'pulang_cepat_menit' => 0,
            'total_potongan_persen' => 0.5,
        ]);

        Attendance::create([
            'employee_id' => $alpha->id,
            'tanggal' => $today,
            'status_kehadiran' => Attendance::STATUS_ALPHA,
            'total_potongan_persen' => 3,
        ]);

        Attendance::create([
            'employee_id' => $izin->id,
            'tanggal' => $today,
            'status_kehadiran' => Attendance::STATUS_IZIN,
            'total_potongan_persen' => 1.75,
        ]);

        Attendance::create([
            'employee_id' => $sakit->id,
            'tanggal' => $today,
            'status_kehadiran' => Attendance::STATUS_SAKIT,
            'total_potongan_persen' => 0,
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertViewHas('absenHariIni', 2)
            ->assertViewHas('telatHariIni', 1)
            ->assertViewHas('persenKehadiran', fn ($value) => abs($value - 40.0) < 0.001)
            ->assertViewHas('persenDisiplin', fn ($value) => abs($value - 50.0) < 0.001);
    }

    public function test_massal_store_rejects_hadir_row_without_check_in_time(): void
    {
        $employee = $this->employee('123', 'Pegawai Uji');
        $tanggal = now()->toDateString();

        $response = $this->followingRedirects()
            ->from(route('attendance.create', ['tanggal' => $tanggal]))
            ->post(route('attendance.storeMassal'), [
                'tanggal' => $tanggal,
                'data' => [
                    $employee->id => [
                        'status' => Attendance::STATUS_HADIR,
                        'masuk' => '',
                        'pulang' => '',
                    ],
                ],
            ]);

        $response->assertOk()
            ->assertSee('Data belum disimpan.')
            ->assertSee('Jam masuk wajib diisi untuk status Hadir.');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_massal_store_saves_all_valid_rows_with_server_calculation(): void
    {
        $hadir = $this->employee('456', 'Hadir');
        $izin = $this->employee('789', 'Izin');
        $dinas = $this->employee('101', 'Dinas');
        $tanggal = now()->toDateString();

        $response = $this->from(route('attendance.create', ['tanggal' => $tanggal]))
            ->post(route('attendance.storeMassal'), [
                'tanggal' => $tanggal,
                'data' => [
                    $hadir->id => [
                        'status' => Attendance::STATUS_HADIR,
                        'masuk' => '08:15',
                        'pulang' => '16:00',
                    ],
                    $izin->id => [
                        'status' => Attendance::STATUS_IZIN,
                    ],
                    $dinas->id => [
                        'status' => 'dinas',
                    ],
                ],
            ]);

        $response->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $hadir->id,
            'status_kehadiran' => Attendance::STATUS_HADIR,
            'terlambat_menit' => 15,
            'total_potongan_persen' => 0.5,
        ]);

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $izin->id,
            'status_kehadiran' => Attendance::STATUS_IZIN,
            'total_potongan_persen' => 1.75,
        ]);

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $dinas->id,
            'status_kehadiran' => Attendance::STATUS_DINAS,
            'total_potongan_persen' => 0,
        ]);
    }
}
