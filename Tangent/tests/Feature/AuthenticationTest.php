<?php

namespace Tests\Feature;

use App\Models\User;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function testRequiredFieldsForRegistration()
    {
        $response = $this->json('POST', 'api/register', ['Accept' => 'application/json']);
        $response->assertJson([
            "success" => false,
            "message" => [
                "name" => ["The name field is required."],
                "email" => ["The email field is required."],
                "password" => ["The password field is required."],
            ]
        ]);
    }

    public function testRepeatPassword()
    {
        $userData = [
            "name" => "John Doe",
            "email" => "doe@example.com",
            "password" => "demo12345"
        ];

        $response = $this->json('POST', 'api/register', $userData, ['Accept' => 'application/json']);
        $response->assertJson([
            "success" => false,
            "message" => [
                "confirm_password" => ["The confirm password field is required."]
            ]
        ]);
    }

    public function testSuccessfulRegistration()
    {
        $userData = User::factory()->create();

        $response = $this->json('POST', 'api/register', $userData->toArray(), ['Accept' => 'application/json']);
        $response->assertJsonStructure([
            "success",
            "message"
        ]);
    }
}
