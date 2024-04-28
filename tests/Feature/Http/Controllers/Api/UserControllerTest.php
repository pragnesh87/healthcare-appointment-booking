<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_can_list_own_appointments(): void
    {
        $this->seed();
        $token = $this->getValidLoginToken();

        $this->withHeader('Authorization', $token)
            ->getJson(route('user.appointment'))
            ->assertStatus(200);
    }
}
