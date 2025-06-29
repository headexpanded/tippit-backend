<?php

namespace App\Http\Requests\Prediction;

use App\Models\Game;
use Illuminate\Foundation\Http\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

class StorePredictionRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Any authenticated user can make predictions
    }

    /**
     * @return array[]
     */
    #[ArrayShape([
        'game_id' => "string[]",
        'predicted_home_score' => "string[]",
        'predicted_away_score' => "string[]",
    ])] public function rules(): array
    {
        return [
            'game_id' => ['required', 'integer', 'exists:games,id'],
            'predicted_home_score' => ['required', 'integer', 'min:0', 'max:20'],
            'predicted_away_score' => ['required', 'integer', 'min:0', 'max:20'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $gameId = $this->input('game_id');
            if ($gameId) {
                $game = Game::with('round')->find($gameId);

                if ($game) {
                    // Check if round is completed
                    if ($game->round && $game->round->is_completed) {
                        $validator->errors()->add('game_id', 'Cannot make predictions for completed rounds.');
                    }

                    // Check if game is locked
                    if ($game->isLocked()) {
                        $validator->errors()->add('game_id', 'Game is locked. Cannot make predictions after lockout time.');
                    }
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    #[ArrayShape([
        'game_id.required' => "string",
        'game_id.integer' => "string",
        'game_id.exists' => "string",
        'predicted_home_score.required' => "string",
        'predicted_home_score.integer' => "string",
        'predicted_home_score.min' => "string",
        'predicted_home_score.max' => "string",
        'predicted_away_score.required' => "string",
        'predicted_away_score.integer' => "string",
        'predicted_away_score.min' => "string",
        'predicted_away_score.max' => "string"
    ])] public function messages(): array
    {
        return [
            'game_id.required' => 'Game ID is required.',
            'game_id.integer' => 'Game ID must be a valid number.',
            'game_id.exists' => 'The selected game does not exist.',
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
