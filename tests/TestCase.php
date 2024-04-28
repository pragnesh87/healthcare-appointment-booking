<?php

namespace Tests;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getValidLoginToken($password = 'secret-password'): string
    {
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        return $loginResponse['data']['token_type'] . " " . $loginResponse['data']['access_token'];
    }
}
