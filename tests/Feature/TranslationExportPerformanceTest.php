<?php

namespace Tests\Feature;

use App\Locale;
use App\Tag;
use App\Translation;
use App\TranslationKey;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TranslationExportPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_locale_export_returns_flat_payload_quickly()
    {
        $locale = factory(Locale::class)->create([
            'code' => 'en',
            'name' => 'English',
        ]);
        $tag = Tag::create([
            'name' => 'Mobile',
            'slug' => 'mobile',
        ]);

        for ($i = 1; $i <= 200; $i++) {
            $key = factory(TranslationKey::class)->create([
                'key' => sprintf('app.export_%03d', $i),
            ]);
            $translation = factory(Translation::class)->create([
                'translation_key_id' => $key->id,
                'locale_id' => $locale->id,
                'value' => 'Exported value ' . $i,
            ]);
            $translation->tags()->attach($tag);
        }

        $start = microtime(true);
        $response = $this->json(
            'GET',
            '/api/translations/export/en?tag=mobile',
            [],
            $this->authHeaders()
        );
        $durationMs = (microtime(true) - $start) * 1000;

        $response->assertStatus(200);

        $payload = json_decode($response->getContent(), true);

        $this->assertEquals(200, $payload['count']);
        $this->assertArrayHasKey('app.export_001', $payload['translations']);
        $this->assertLessThan(500, $durationMs);
    }
}
