<?php

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

class UpdateGameScoreRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    /**
     * @return array[]
     */
    #[ArrayShape([
        'home_score' => "string[]",
        'away_score' => "string[]",
    ])] public function rules(): array
    {
        return [
            'home_score' => ['required', 'integer', 'min:0'],
            'away_score' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    #[ArrayShape([
        'home_score.required' => "string",
        'home_score.integer' => "string",
        'home_score.min' => "string",
        'away_score.required' => "string",
        'away_score.integer' => "string",
        'away_score.min' => "string",
    ])] public function messages(): array
    {
        return [
            'home_score.required' => 'Please enter the home team score.',
            'home_score.integer' => 'Home team score must be a whole number.',
            'home_score.min' => 'Home team score cannot be negative.',
            'away_score.required' => 'Please enter the away team score.',
            'away_score.integer' => 'Away team score must be a whole number.',
            'away_score.min' => 'Away team score cannot be negative.',
        ];
    }
}
