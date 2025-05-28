<?php

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

class StoreGameRequest extends FormRequest
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
        'home_team_id' => "string[]",
        'away_team_id' => "string[]",
        'match_date' => "string[]",
        'match_time' => "string[]",
        'lockout_time' => "string[]",
        'competition' => "string[]",
        'venue' => "string[]"
    ])] public function rules(): array
    {
        return [
            'home_team_id' => ['required', 'exists:teams,id'],
            'away_team_id' => ['required', 'exists:teams,id', 'different:home_team_id'],
            'match_date' => ['required', 'date', 'after_or_equal:today'],
            'match_time' => ['required', 'date_format:H:i'],
            'lockout_time' => ['required', 'date', 'before:match_date'],
            'competition' => ['required', 'string', 'max:255'],
            'venue' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    #[ArrayShape([
        'home_team_id.required' => "string",
        'away_team_id.required' => "string",
        'away_team_id.different' => "string",
        'match_date.required' => "string",
        'match_date.after_or_equal' => "string",
        'match_time.required' => "string",
        'lockout_time.required' => "string",
        'lockout_time.before' => "string",
        'competition.required' => "string"
    ])] public function messages(): array
    {
        return [
            'home_team_id.required' => 'Please select a home team.',
            'away_team_id.required' => 'Please select an away team.',
            'away_team_id.different' => 'Home and away teams must be different.',
            'match_date.required' => 'Please select a match date.',
            'match_date.after_or_equal' => 'Match date must be today or a future date.',
            'match_time.required' => 'Please select a match time.',
            'lockout_time.required' => 'Please select a lockout time.',
            'lockout_time.before' => 'Lockout time must be before the match date.',
            'competition.required' => 'Please specify the competition.',
        ];
    }
}
