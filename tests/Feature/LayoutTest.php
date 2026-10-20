<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_active_pages_share_the_same_app_shell(): void
    {
        foreach (['dashboard', 'attendance.create', 'attendance.rekap', 'employee.create'] as $route) {
            $response = $this->get(route($route));

            $response->assertOk()
                ->assertSee('site-nav', false)
                ->assertSee('Lewati ke konten utama')
                ->assertSee('Dinas Pekerjaan Umum')
                ->assertSee('Keluar');
        }
    }

    public function test_report_shows_a_no_data_notice_instead_of_a_full_score(): void
    {
        Employee::create([
            'nip' => '123',
            'nama' => 'Pegawai Uji',
            'jabatan' => 'Staf',
            'tpp_pagu' => 0,
        ]);

        $response = $this->get(route('attendance.rekap', [
            'bulan' => now()->month,
            'tahun' => now()->year,
        ]));

        $response->assertOk()
            ->assertSee('Belum ada absensi pada periode ini.')
            ->assertSee('&mdash;', false);
    }
}
