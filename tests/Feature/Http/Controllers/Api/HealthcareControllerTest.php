<?php

namespace Tests\Feature\Http\Controllers\Api;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HealthcareControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_see_healthcarelist_without_login(): void
    {
        $this->getJson(route('healthcares.index'))
            ->assertStatus(401);
    }
    public function test_user_cannot_see_healthcarelist_without_valid_token(): void
    {
        $this->getJson(route('healthcares.index'))
            ->assertStatus(401);

        $user = User::factory()->create([
            'password' => Hash::make($password = 'secret-password'),
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ])->assertStatus(200);

        $this->withHeader('Authorization', $loginResponse['data']['token_type'] . 'some-random-token')
            ->getJson(route('healthcares.index'))
            ->assertStatus(200);
    }

    public function test_user_can_see_healthcarelist_with_valid_token()
    {
        $user = User::factory()->create([
            'password' => Hash::make($password = 'secret-password'),
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ])->assertStatus(200);

        $this->withHeader('Authorization', $loginResponse['data']['token_type'] . $loginResponse['data']['access_token'])
            ->getJson(route('healthcares.index'))
            ->assertStatus(200);
    }
}
