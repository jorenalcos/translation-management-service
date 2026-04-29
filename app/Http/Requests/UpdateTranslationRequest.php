<?php

namespace App\Http\Requests;

use App\Locale;
use App\Translation;
use App\TranslationKey;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTranslationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'key' => 'sometimes|required_without:translation_key_id|string|max:191',
            'translation_key_id' => 'sometimes|required_without:key|integer|exists:translation_keys,id',
            'description' => 'nullable|string',
            'locale' => 'sometimes|required_without:locale_id|string|max:12',
            'locale_id' => 'sometimes|required_without:locale|integer|exists:locales,id',
            'locale_name' => 'nullable|string|max:191',
            'value' => 'sometimes|required|string',
            'is_reviewed' => 'boolean',
            'tags' => 'array',
            'tags.*' => 'string|max:64',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                return;
            }

            $this->validateExclusiveIdentifiers($validator);

            if ($validator->errors()->any()) {
                return;
            }

            $this->validateUniqueKeyLocalePair($validator);
        });
    }

    private function validateExclusiveIdentifiers($validator)
    {
        if ($this->filled('key') && $this->filled('translation_key_id')) {
            $validator->errors()->add('translation_key_id', 'Provide either key or translation_key_id, not both.');
        }

        if ($this->filled('locale') && $this->filled('locale_id')) {
            $validator->errors()->add('locale_id', 'Provide either locale or locale_id, not both.');
        }
    }

    private function validateUniqueKeyLocalePair($validator)
    {
        $translation = Translation::find($this->route('translation'));

        if (!$translation) {
            return;
        }

        $translationKeyId = $this->targetTranslationKeyId($translation);
        $localeId = $this->targetLocaleId($translation);

        if (!$translationKeyId || !$localeId) {
            return;
        }

        $duplicateExists = Translation::where('translation_key_id', $translationKeyId)
            ->where('locale_id', $localeId)
            ->where('id', '<>', $translation->id)
            ->exists();

        if ($duplicateExists) {
            $validator->errors()->add('translation', 'A translation for this key and locale already exists.');
        }
    }

    private function targetTranslationKeyId(Translation $translation)
    {
        if ($this->filled('translation_key_id')) {
            return (int) $this->input('translation_key_id');
        }

        if ($this->filled('key')) {
            $translationKey = TranslationKey::where('key', trim($this->input('key')))->first();

            return $translationKey ? $translationKey->id : null;
        }

        return $translation->translation_key_id;
    }

    private function targetLocaleId(Translation $translation)
    {
        if ($this->filled('locale_id')) {
            return (int) $this->input('locale_id');
        }

        if ($this->filled('locale')) {
            $locale = Locale::where('code', strtolower(trim($this->input('locale'))))->first();

            return $locale ? $locale->id : null;
        }

        return $translation->locale_id;
    }
}
