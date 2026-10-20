<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_the_user_panel(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Tambah Pengguna')
            ->assertSee('Daftar Pengguna')
            ->assertSee('Pengguna');
    }

    public function test_operator_cannot_open_the_user_panel(): void
    {
        $this->actingAs($this->operator())
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_sees_the_user_management_link(): void
    {
        $this->actingAs($this->admin())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pengguna');

        $this->actingAs($this->operator())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Pengguna');
    }

    public function test_admin_can_create_an_operator(): void
    {
        $response = $this->actingAs($this->admin())->post(route('admin.users.store'), [
            'name' => 'Operator Baru',
            'email' => 'OPERATOR@EXAMPLE.COM',
            'role' => User::ROLE_OPERATOR,
            'password' => 'kata-sandi-operator-123',
            'password_confirmation' => 'kata-sandi-operator-123',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'operator@example.com')->firstOrFail();

        $this->assertSame('Operator Baru', $user->name);
        $this->assertSame(User::ROLE_OPERATOR, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('kata-sandi-operator-123', $user->password));
    }

    public function test_admin_can_update_a_user_and_role(): void
    {
        $target = $this->operator();

        $response = $this->actingAs($this->admin())->put(route('admin.users.update', $target), [
            'name' => 'Nama Baru',
            'email' => 'baru@example.com',
            'role' => User::ROLE_ADMIN,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.users.edit', $target));

        $target->refresh();

        $this->assertSame('Nama Baru', $target->name);
        $this->assertSame('baru@example.com', $target->email);
        $this->assertSame(User::ROLE_ADMIN, $target->role);
        $this->assertTrue($target->is_active);
    }

    public function test_admin_can_deactivate_another_user(): void
    {
        $target = $this->operator();

        $response = $this->actingAs($this->admin())->put(route('admin.users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'role' => User::ROLE_OPERATOR,
            'is_active' => '0',
        ]);

        $response->assertRedirect(route('admin.users.edit', $target));
        $this->assertFalse($target->fresh()->is_active);
    }

    public function test_admin_can_open_the_edit_page(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.users.edit', $this->operator()))
            ->assertOk()
            ->assertSee('Profil &amp; Akses', false)
            ->assertSee('Reset Kata Sandi');
    }

    public function test_admin_can_reset_a_user_password(): void
    {
        $target = $this->operator();

        $response = $this->actingAs($this->admin())->put(route('admin.users.password', $target), [
            'password' => 'kata-sandi-baru-123',
            'password_confirmation' => 'kata-sandi-baru-123',
        ]);

        $response->assertRedirect(route('admin.users.edit', $target));

        $target->refresh();

        $this->assertTrue(Hash::check('kata-sandi-baru-123', $target->password));
        $this->assertNull($target->remember_token);
    }

    public function test_admin_cannot_demote_or_deactivate_their_own_account(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => User::ROLE_OPERATOR,
            'is_active' => '1',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertSame(User::ROLE_ADMIN, $admin->fresh()->role);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => User::ROLE_ADMIN,
            'is_active' => '0',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_new_user_password_must_be_at_least_twelve_characters(): void
    {
        $response = $this->actingAs($this->admin())->post(route('admin.users.store'), [
            'name' => 'Operator Pendek',
            'email' => 'pendek@example.com',
            'role' => User::ROLE_OPERATOR,
            'password' => 'pendek',
            'password_confirmation' => 'pendek',
            'is_active' => '1',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'pendek@example.com']);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'password' => 'kata-sandi-uji-123',
            'is_active' => false,
        ]);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'kata-sandi-uji-123',
        ]);

        $response->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_session_is_terminated(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function operator(): User
    {
        return User::factory()->create(['role' => User::ROLE_OPERATOR]);
    }
}
