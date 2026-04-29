<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTranslationRequest extends FormRequest
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
            'key' => 'required_without:translation_key_id|string|max:191',
            'translation_key_id' => 'required_without:key|integer|exists:translation_keys,id',
            'description' => 'nullable|string',
            'locale' => 'required_without:locale_id|string|max:12',
            'locale_id' => 'required_without:locale|integer|exists:locales,id',
            'locale_name' => 'nullable|string|max:191',
            'value' => 'required|string',
            'is_reviewed' => 'boolean',
            'tags' => 'array',
            'tags.*' => 'string|max:64',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled('key') && $this->filled('translation_key_id')) {
                $validator->errors()->add('translation_key_id', 'Provide either key or translation_key_id, not both.');
            }

            if ($this->filled('locale') && $this->filled('locale_id')) {
                $validator->errors()->add('locale_id', 'Provide either locale or locale_id, not both.');
            }
        });
    }
}
