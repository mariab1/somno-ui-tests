<?php

namespace Tests\Browser\Helpers;

use App\Models\User;

class TestHelper
{
    public static function createAdminUser(array $attributes = []): User
    {
        $user = factory(User::class)->create($attributes);
        $user->setRole(1);
        return $user;
    }
}
