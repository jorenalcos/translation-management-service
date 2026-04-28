<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_for_api_access()
    {
        $response = $this->json('POST', '/api/register', [
            'name' => 'Jane Translator',
            'email' => 'jane@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'email'],
            ]);

        $payload = json_decode($response->getContent(), true);

        $this->assertNotEmpty($payload['token']);
        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
        ]);
    }

    public function test_user_can_login_and_fetch_current_user()
    {
        factory(User::class)->create([
            'email' => 'jane@example.com',
            'password' => Hash::make('secret123'),
            'api_token' => null,
        ]);

        $login = $this->json('POST', '/api/login', [
            'email' => 'jane@example.com',
            'password' => 'secret123',
        ]);

        $login->assertStatus(200)->assertJsonStructure(['token']);

        $payload = json_decode($login->getContent(), true);

        $this->json('GET', '/api/user', [], [
            'Authorization' => 'Bearer ' . $payload['token'],
            'Accept' => 'application/json',
        ])->assertStatus(200)->assertJson([
            'email' => 'jane@example.com',
        ]);
    }

    public function test_logout_revokes_current_token()
    {
        $user = factory(User::class)->create();

        $this->json('POST', '/api/logout', [], $this->authHeaders($user))
            ->assertStatus(204);

        $this->assertNull($user->fresh()->api_token);
    }
}
