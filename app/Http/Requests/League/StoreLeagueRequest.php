<?php

namespace App\Http\Requests\League;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

class StoreLeagueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array[]
     */
    #[ArrayShape([
        'name' => "string[]",
        'description' => "string[]",
    ])] public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'min:3'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    #[ArrayShape([
        'name.required' => "string",
        'name.string' => "string",
        'name.max' => "string",
        'name.min' => "string",
        'description.string' => "string",
        'description.max' => "string"
    ])] public function messages(): array
    {
        return [
            'name.required' => 'Please enter a name for your mini league.',
            'name.string' => 'The name must be text.',
            'name.max' => 'The name cannot exceed 50 characters.',
            'name.min' => 'The name must be at least 3 characters.',
            'description.string' => 'The description must be text.',
            'description.max' => 'The description cannot exceed 255 characters.',
        ];
    }
}
