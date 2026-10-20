<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        foreach ([
            route('dashboard'),
            route('attendance.create'),
            route('attendance.rekap'),
            route('employee.create'),
            route('attendance.downloadPdf'),
            route('admin.users.index'),
        ] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_login_page_can_be_rendered(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Masuk ke Rekap Absen')
            ->assertDontSee('Navigasi utama');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => 'rahasia-uji-123']);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'rahasia-uji-123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'kata-sandi-salah',
        ]);

        $response->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_failures(): void
    {
        $user = User::factory()->create();

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'kata-sandi-salah',
            ]);
        }

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'kata-sandi-salah',
        ]);

        $response->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertStringContainsString('Terlalu banyak', session('errors')->first('email'));
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_admin_command_creates_a_user_with_a_hashed_password(): void
    {
        $this->artisan('app:create-admin', ['email' => 'ADMIN@EXAMPLE.COM'])
            ->expectsQuestion('Kata sandi (minimal 12 karakter)', 'kata-sandi-kuat-123')
            ->expectsQuestion('Ulangi kata sandi', 'kata-sandi-kuat-123')
            ->assertSuccessful();

        $user = User::first();

        $this->assertSame('admin@example.com', $user->email);
        $this->assertSame(User::ROLE_ADMIN, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertNotSame('kata-sandi-kuat-123', $user->password);
        $this->assertTrue(Hash::check('kata-sandi-kuat-123', $user->password));
    }
}
