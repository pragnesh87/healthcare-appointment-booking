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
    public function test_user_cannot_see_healthcarelist_with_invalid_token(): void
    {
        $this->withHeader('Authorization', 'Bearer some-random-token')
            ->getJson(route('healthcares.index'))
            ->assertStatus(401);
    }

    public function test_user_can_see_healthcarelist_with_valid_token()
    {
        $token = $this->getValidLoginToken();

        $this->withHeader('Authorization', $token)
            ->getJson(route('healthcares.index'))
            ->assertStatus(200);
    }
}
