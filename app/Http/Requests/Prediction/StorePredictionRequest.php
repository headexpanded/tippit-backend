<?php

namespace App\Http\Requests\Prediction;

use Illuminate\Foundation\Http\FormRequest;

class StorePredictionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Any authenticated user can make predictions
    }

    public function rules(): array
    {
        return [
            'predicted_home_score' => ['required', 'integer', 'min:0', 'max:20'],
            'predicted_away_score' => ['required', 'integer', 'min:0', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'predicted_home_score.required' => 'Please predict the home team score.',
            'predicted_home_score.integer' => 'Home team score must be a whole number.',
            'predicted_home_score.min' => 'Home team score cannot be negative.',
            'predicted_home_score.max' => 'Home team score cannot exceed 20.',
            'predicted_away_score.required' => 'Please predict the away team score.',
            'predicted_away_score.integer' => 'Away team score must be a whole number.',
            'predicted_away_score.min' => 'Away team score cannot be negative.',
            'predicted_away_score.max' => 'Away team score cannot exceed 20.',
        ];
    }
}
