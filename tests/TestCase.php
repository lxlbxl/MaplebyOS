<?php

namespace Tests;

use App\Models\User;
use App\Support\Access;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** A signed-in staff member with one of the roles in App\Support\Access. */
    protected function staff(string $role = Access::ROLE_CEO): User
    {
        Access::syncRoles();
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
