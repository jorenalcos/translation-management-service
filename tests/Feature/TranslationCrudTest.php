<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TranslationCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_update_show_and_delete_translation()
    {
        $create = $this->json('POST', '/api/translations', [
            'key' => 'auth.login.title',
            'description' => 'Login page title',
            'locale' => 'en',
            'locale_name' => 'English',
            'value' => 'Log in',
            'is_reviewed' => true,
            'tags' => ['web', 'auth'],
        ], $this->authHeaders());

        $create->assertStatus(201);

        $created = json_decode($create->getContent(), true);
        $translationId = $created['data']['id'];

        $this->assertDatabaseHas('translation_keys', [
            'key' => 'auth.login.title',
        ]);
        $this->assertDatabaseHas('translations', [
            'id' => $translationId,
            'value' => 'Log in',
            'is_reviewed' => true,
        ]);

        $this->json('GET', '/api/translations/' . $translationId, [], $this->authHeaders())
            ->assertStatus(200)
            ->assertJsonFragment([
                'value' => 'Log in',
            ]);

        $update = $this->json('PUT', '/api/translations/' . $translationId, [
            'value' => 'Sign in',
            'tags' => ['web', 'auth', 'release'],
        ], $this->authHeaders());

        $update->assertStatus(200)->assertJsonFragment([
            'value' => 'Sign in',
        ]);

        $this->assertDatabaseHas('tags', [
            'slug' => 'release',
        ]);

        $this->json('DELETE', '/api/translations/' . $translationId, [], $this->authHeaders())
            ->assertStatus(204);

        $this->assertDatabaseMissing('translations', [
            'id' => $translationId,
        ]);
    }

    public function test_translation_routes_require_authentication()
    {
        $this->json('GET', '/api/translations')->assertStatus(401);
    }

    public function test_create_rejects_mixed_key_and_locale_identifiers()
    {
        $headers = $this->authHeaders();

        $existing = $this->json('POST', '/api/translations', [
            'key' => 'auth.login.title',
            'locale' => 'en',
            'locale_name' => 'English',
            'value' => 'Log in',
        ], $headers);

        $existing->assertStatus(201);
        $payload = json_decode($existing->getContent(), true);

        $response = $this->json('POST', '/api/translations', [
            'key' => 'auth.login.title',
            'translation_key_id' => $payload['data']['translation_key_id'],
            'description' => 'Login page title',
            'locale' => 'en',
            'locale_id' => $payload['data']['locale_id'],
            'locale_name' => 'English',
            'value' => 'Log in',
        ], $headers);

        $response->assertStatus(422);

        $errors = json_decode($response->getContent(), true);
        $this->assertEquals(
            'Provide either key or translation_key_id, not both.',
            $errors['errors']['translation_key_id'][0]
        );
        $this->assertEquals(
            'Provide either locale or locale_id, not both.',
            $errors['errors']['locale_id'][0]
        );
    }

    public function test_update_cannot_move_translation_to_existing_key_locale_pair()
    {
        $headers = $this->authHeaders();

        $this->json('POST', '/api/translations', [
            'key' => 'auth.login.title',
            'locale' => 'en',
            'locale_name' => 'English',
            'value' => 'Log in',
        ], $headers)->assertStatus(201);

        $spanish = $this->json('POST', '/api/translations', [
            'key' => 'auth.login.title',
            'locale' => 'es',
            'locale_name' => 'Spanish',
            'value' => 'Iniciar sesion',
        ], $headers);

        $spanish->assertStatus(201);
        $payload = json_decode($spanish->getContent(), true);

        $response = $this->json('PUT', '/api/translations/' . $payload['data']['id'], [
            'locale' => 'en',
        ], $headers);

        $response->assertStatus(422);

        $errors = json_decode($response->getContent(), true);
        $this->assertEquals(
            'A translation for this key and locale already exists.',
            $errors['errors']['translation'][0]
        );
    }
}
