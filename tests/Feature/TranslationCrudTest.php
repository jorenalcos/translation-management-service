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
}
