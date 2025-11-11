<?php

declare(strict_types=1);

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function newUser(array $attributes = []): TestUser
    {
        $user = factory(User::class)->create($attributes);

        return new TestUser($user);
    }
}
