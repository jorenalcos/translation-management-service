<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class TranslationScaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::disableQueryLog();

        if (DB::table('translations')->count() >= 100000) {
            $this->command->info('Translation scale data already exists.');

            return;
        }

        $now = Carbon::now();
        $chunkSize = 150;
        $locales = [
            ['code' => 'en', 'name' => 'English'],
            ['code' => 'es', 'name' => 'Spanish'],
            ['code' => 'fr', 'name' => 'French'],
            ['code' => 'de', 'name' => 'German'],
            ['code' => 'it', 'name' => 'Italian'],
            ['code' => 'pt', 'name' => 'Portuguese'],
            ['code' => 'nl', 'name' => 'Dutch'],
            ['code' => 'ja', 'name' => 'Japanese'],
            ['code' => 'ko', 'name' => 'Korean'],
            ['code' => 'zh', 'name' => 'Chinese'],
        ];

        foreach ($locales as $locale) {
            DB::table('locales')->updateOrInsert(
                ['code' => $locale['code']],
                [
                    'name' => $locale['name'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        foreach (['web', 'mobile', 'admin', 'checkout', 'marketing', 'email', 'errors', 'auth'] as $tag) {
            DB::table('tags')->updateOrInsert(
                ['slug' => $tag],
                [
                    'name' => ucfirst($tag),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $existingKeys = DB::table('translation_keys')->count();
        if ($existingKeys < 10000) {
            $rows = [];

            for ($i = $existingKeys + 1; $i <= 10000; $i++) {
                $rows[] = [
                    'key' => sprintf('app.feature_%05d.label', $i),
                    'description' => 'Seeded translation key ' . $i,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($rows) === $chunkSize) {
                    DB::table('translation_keys')->insert($rows);
                    $rows = [];
                }
            }

            if (!empty($rows)) {
                DB::table('translation_keys')->insert($rows);
            }
        }

        $localeIds = DB::table('locales')->orderBy('id')->pluck('id', 'code')->all();
        $tagIds = DB::table('tags')->orderBy('id')->pluck('id')->all();
        $translationRows = [];
        $createdTranslationIds = [];

        foreach (DB::table('translation_keys')->orderBy('id')->pluck('id') as $keyId) {
            foreach ($localeIds as $localeCode => $localeId) {
                $translationRows[] = [
                    'translation_key_id' => $keyId,
                    'locale_id' => $localeId,
                    'value' => 'Seeded ' . strtoupper($localeCode) . ' copy for key ' . $keyId,
                    'is_reviewed' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($translationRows) === $chunkSize) {
                    DB::table('translations')->insert($translationRows);
                    $translationRows = [];
                }
            }
        }

        if (!empty($translationRows)) {
            DB::table('translations')->insert($translationRows);
        }

        foreach (DB::table('translations')->orderBy('id')->pluck('id') as $translationId) {
            $createdTranslationIds[] = $translationId;
        }

        $pivotRows = [];
        foreach ($createdTranslationIds as $index => $translationId) {
            $firstTag = $tagIds[$index % count($tagIds)];
            $secondTag = $tagIds[($index + 3) % count($tagIds)];

            foreach ([$firstTag, $secondTag] as $tagId) {
                $pivotRows[] = [
                    'tag_id' => $tagId,
                    'translation_id' => $translationId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (count($pivotRows) >= $chunkSize) {
                DB::table('tag_translation')->insert($pivotRows);
                $pivotRows = [];
            }
        }

        if (!empty($pivotRows)) {
            DB::table('tag_translation')->insert($pivotRows);
        }

        $this->command->info('Seeded ' . DB::table('translations')->count() . ' translations.');
    }
}
