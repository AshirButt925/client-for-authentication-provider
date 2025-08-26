<?php

namespace ClientForAuthenticationProvider\Tests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_mini_app_create_or_login_user_with_valid_data()
    {
        $userData = [
            'user_data' => [
                'email' => 'test@example.com',
                'mobile' => '1234567890',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'country_code' => '+92',
                'country_iso_code' => 'PK',
                'password' => 'password123'
            ]
        ];

        $response = $this->postJson('/api/create-or-login-user', $userData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'token',
                    'data' => [
                        'id',
                        'name',
                        'email',
                        'phone'
                    ]
                ]);
    }

    public function test_mini_app_create_or_login_user_with_invalid_data()
    {
        $userData = [
            'user_data' => [
                'email' => 'invalid-email',
                'mobile' => '',
                'first_name' => '',
                'last_name' => ''
            ]
        ];

        $response = $this->postJson('/api/create-or-login-user', $userData);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'errors'
                ]);
    }
}
