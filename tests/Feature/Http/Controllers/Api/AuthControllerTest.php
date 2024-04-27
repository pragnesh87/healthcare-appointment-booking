<?php

namespace Tests\Feature\Http\Controllers\Api;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_a_user_can_register()
    {
        $user = User::factory()->make()->toArray();
        $user['password'] = 'password';
        $user['password_confirmation'] = 'password';

        $this->postJson('/api/register', $user)
            ->assertCreated();

        $this->assertDatabaseHas('users', [
            'name' => $user['name'],
            'email' => $user['email']
        ]);
    }

    public function test_registation_fail_if_password_is_missing()
    {
        $user = User::factory()->make()->toArray();

        $this->postJson('/api/register', $user)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_user_cannot_login_with_incorrect_credentials()
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password'
        ])
            ->assertStatus(401);
    }

    public function test_user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'password' => Hash::make($password = 'secret-password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ])->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'access_token',
                'token_type'
            ]
        ]);
    }
}
