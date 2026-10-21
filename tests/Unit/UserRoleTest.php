<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    public function test_supported_roles_are_admin_and_operator(): void
    {
        $this->assertSame([User::ROLE_ADMIN, User::ROLE_OPERATOR], User::roles());
    }

    public function test_admin_role_is_detected(): void
    {
        $admin = new User(['role' => User::ROLE_ADMIN]);
        $operator = new User(['role' => User::ROLE_OPERATOR]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($operator->isAdmin());
    }

    public function test_role_labels_are_human_readable(): void
    {
        $this->assertSame('Admin', (new User(['role' => User::ROLE_ADMIN]))->roleLabel());
        $this->assertSame('Operator', (new User(['role' => User::ROLE_OPERATOR]))->roleLabel());
    }
}
