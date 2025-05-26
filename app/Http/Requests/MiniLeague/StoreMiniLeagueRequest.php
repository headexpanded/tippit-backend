<?php

namespace App\Http\Requests\MiniLeague;

use Illuminate\Foundation\Http\FormRequest;

class StoreMiniLeagueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Any authenticated user can create a mini league
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'min:3'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
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
