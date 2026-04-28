<?php

namespace App\Http\Requests;

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
}
