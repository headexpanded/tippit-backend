<?php

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGameScoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'home_score' => ['required', 'integer', 'min:0'],
            'away_score' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
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
