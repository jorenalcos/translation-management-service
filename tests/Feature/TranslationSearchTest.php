<?php

namespace Tests\Feature;

use App\Locale;
use App\Tag;
use App\Translation;
use App\TranslationKey;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TranslationSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_translations_can_be_filtered_by_locale_tag_key_and_text()
    {
        $english = factory(Locale::class)->create([
            'code' => 'en',
            'name' => 'English',
        ]);
        $spanish = factory(Locale::class)->create([
            'code' => 'es',
            'name' => 'Spanish',
        ]);
        $mobile = Tag::create([
            'name' => 'Mobile',
            'slug' => 'mobile',
        ]);
        $web = Tag::create([
            'name' => 'Web',
            'slug' => 'web',
        ]);

        $welcomeKey = factory(TranslationKey::class)->create([
            'key' => 'home.welcome',
        ]);
        $checkoutKey = factory(TranslationKey::class)->create([
            'key' => 'checkout.button',
        ]);

        $match = factory(Translation::class)->create([
            'translation_key_id' => $welcomeKey->id,
            'locale_id' => $english->id,
            'value' => 'Welcome aboard',
        ]);
        $match->tags()->attach($mobile);

        $wrongTag = factory(Translation::class)->create([
            'translation_key_id' => $checkoutKey->id,
            'locale_id' => $english->id,
            'value' => 'Welcome checkout',
        ]);
        $wrongTag->tags()->attach($web);

        factory(Translation::class)->create([
            'translation_key_id' => $welcomeKey->id,
            'locale_id' => $spanish->id,
            'value' => 'Bienvenido',
        ]);

        $response = $this->json(
            'GET',
            '/api/translations?q=Welcome&locale=en&tag=mobile&key=home',
            [],
            $this->authHeaders()
        );

        $response->assertStatus(200);

        $payload = json_decode($response->getContent(), true);

        $this->assertEquals(1, $payload['total']);
        $this->assertEquals('Welcome aboard', $payload['data'][0]['value']);
        $this->assertEquals('home.welcome', $payload['data'][0]['translation_key']['key']);
    }
}
