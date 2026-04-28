<?php

namespace App\Repositories;

use App\Locale;
use App\Tag;
use App\Translation;
use App\TranslationKey;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TranslationRepository
{
    public function paginate(array $filters, $perPage)
    {
        return $this->filteredQuery($filters)
            ->orderBy('translation_keys.key')
            ->orderBy('locales.code')
            ->paginate($perPage);
    }

    public function find($id)
    {
        return Translation::with(['locale', 'translationKey', 'tags'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $locale = $this->resolveLocale($data);
            $translationKey = $this->resolveTranslationKey($data);

            $translation = Translation::updateOrCreate(
                [
                    'translation_key_id' => $translationKey->id,
                    'locale_id' => $locale->id,
                ],
                [
                    'value' => $data['value'],
                    'is_reviewed' => Arr::get($data, 'is_reviewed', false),
                ]
            );

            $this->syncTags($translation, Arr::get($data, 'tags', []));

            return $translation->fresh(['locale', 'translationKey', 'tags']);
        });
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $translation = $this->find($id);
            $locale = $this->hasLocaleInput($data) ? $this->resolveLocale($data) : $translation->locale;
            $translationKey = $this->hasKeyInput($data) ? $this->resolveTranslationKey($data) : $translation->translationKey;

            $translation->fill([
                'translation_key_id' => $translationKey->id,
                'locale_id' => $locale->id,
                'value' => Arr::get($data, 'value', $translation->value),
                'is_reviewed' => Arr::get($data, 'is_reviewed', $translation->is_reviewed),
            ]);
            $translation->save();

            if (array_key_exists('tags', $data)) {
                $this->syncTags($translation, $data['tags']);
            }

            return $translation->fresh(['locale', 'translationKey', 'tags']);
        });
    }

    public function delete($id)
    {
        $translation = $this->find($id);
        $translation->tags()->detach();
        $translation->delete();
    }

    public function export($localeCode, array $filters = [])
    {
        $query = Translation::query()
            ->select([
                'translation_keys.key as translation_key',
                'translations.value as translation_value',
            ])
            ->join('translation_keys', 'translation_keys.id', '=', 'translations.translation_key_id')
            ->join('locales', 'locales.id', '=', 'translations.locale_id')
            ->where('locales.code', $localeCode)
            ->orderBy('translation_keys.key');

        $this->applyTagFilter($query, Arr::get($filters, 'tag'));

        $items = [];
        foreach ($query->get() as $row) {
            $items[$row->translation_key] = $row->translation_value;
        }

        return $items;
    }

    private function filteredQuery(array $filters)
    {
        $query = Translation::query()
            ->select('translations.*')
            ->with(['locale', 'translationKey', 'tags'])
            ->join('translation_keys', 'translation_keys.id', '=', 'translations.translation_key_id')
            ->join('locales', 'locales.id', '=', 'translations.locale_id');

        if (Arr::get($filters, 'locale')) {
            $query->where('locales.code', Arr::get($filters, 'locale'));
        }

        if (Arr::get($filters, 'key')) {
            $query->where('translation_keys.key', 'like', '%' . Arr::get($filters, 'key') . '%');
        }

        if (Arr::get($filters, 'q')) {
            $term = Arr::get($filters, 'q');
            $query->where(function ($inner) use ($term) {
                $inner->where('translation_keys.key', 'like', '%' . $term . '%')
                    ->orWhere('translations.value', 'like', '%' . $term . '%');
            });
        }

        $this->applyTagFilter($query, Arr::get($filters, 'tag'));

        return $query;
    }

    private function applyTagFilter($query, $tagFilter)
    {
        if (!$tagFilter) {
            return;
        }

        $tags = is_array($tagFilter) ? $tagFilter : explode(',', $tagFilter);
        $tags = array_filter(array_map('trim', $tags));

        if (empty($tags)) {
            return;
        }

        $query->whereHas('tags', function ($tagQuery) use ($tags) {
            $tagQuery->whereIn('slug', $tags)->orWhereIn('name', $tags);
        });
    }

    private function resolveLocale(array $data)
    {
        if (Arr::get($data, 'locale_id')) {
            return Locale::findOrFail($data['locale_id']);
        }

        $code = strtolower(trim($data['locale']));

        return Locale::firstOrCreate(
            ['code' => $code],
            ['name' => Arr::get($data, 'locale_name', strtoupper($code))]
        );
    }

    private function resolveTranslationKey(array $data)
    {
        if (Arr::get($data, 'translation_key_id')) {
            return TranslationKey::findOrFail($data['translation_key_id']);
        }

        return TranslationKey::firstOrCreate(
            ['key' => trim($data['key'])],
            ['description' => Arr::get($data, 'description')]
        );
    }

    private function syncTags(Translation $translation, array $tags)
    {
        $tagIds = [];

        foreach ($tags as $tagName) {
            $tagName = trim($tagName);

            if ($tagName === '') {
                continue;
            }

            $tag = Tag::firstOrCreate(
                ['slug' => Str::slug($tagName)],
                ['name' => $tagName]
            );

            $tagIds[] = $tag->id;
        }

        $translation->tags()->sync($tagIds);
    }

    private function hasLocaleInput(array $data)
    {
        return Arr::get($data, 'locale') || Arr::get($data, 'locale_id');
    }

    private function hasKeyInput(array $data)
    {
        return Arr::get($data, 'key') || Arr::get($data, 'translation_key_id');
    }
}
