<?php

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function authHeaders(User $user = null)
    {
        $user = $user ?: factory(User::class)->create([
            'api_token' => Str::random(80),
        ]);

        return [
            'Authorization' => 'Bearer ' . $user->api_token,
            'Accept' => 'application/json',
        ];
    }
}
