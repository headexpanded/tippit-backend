<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use JetBrains\PhpStorm\ArrayShape;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Users can only update their own account
        return $this->user()->id === $this->route('player')->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    #[ArrayShape(['username' => "array", 'email' => "array", 'supported_team_id' => "string[]"])]
    public function rules(): array
    {
        $userId = $this->route('player')->id;

        return [
            'username' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'supported_team_id' => [
                'sometimes',
                'nullable',
                'exists:teams,id',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    #[ArrayShape([
        'username.unique' => "string",
        'email.unique' => "string",
        'email.email' => "string",
        'supported_team_id.exists' => "string"
    ])] public function messages(): array
    {
        return [
            'username.unique' => 'This username is already taken.',
            'email.unique' => 'This email address is already registered.',
            'email.email' => 'Please enter a valid email address.',
            'supported_team_id.exists' => 'The selected team does not exist.',
        ];
    }
}
