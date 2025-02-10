<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public const MAX_TITLE = 255;

    public const MAX_DESCRIPTION = 2000;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return self::staticRules();
    }

    public static function staticRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:'.self::MAX_TITLE],
            'description' => ['required', 'string', 'max:'.self::MAX_DESCRIPTION],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'due_at' => ['required', 'date'],
        ];
    }
}
