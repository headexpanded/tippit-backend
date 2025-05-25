<?php

namespace App\Services;

use App\Events\Prediction\PredictionCreated;
use App\Events\Prediction\PredictionUpdated;
use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PredictionService extends BaseService
{
    public function __construct(Prediction $model)
    {
        parent::__construct($model);
    }

    public function createPrediction(User $user, Game $game, array $data): Prediction
    {
        // Check if game is locked
        if (now()->isAfter($game->lockout_time)) {
            throw new \Exception('Game is locked. Cannot make predictions.');
        }

        // Check if prediction already exists
        if ($this->model->where('user_id', $user->id)
            ->where('game_id', $game->id)
            ->exists()) {
            throw new \Exception('Prediction already exists for this game.');
        }

        $prediction = $this->model->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'predicted_home_score' => $data['predicted_home_score'],
            'predicted_away_score' => $data['predicted_away_score'],
        ]);

        event(new PredictionCreated($prediction));

        return $prediction;
    }

    public function updatePrediction(Prediction $prediction, array $data): Prediction
    {
        $game = $prediction->game;

        // Check if game is locked
        if (now()->isAfter($game->lockout_time)) {
            throw new \Exception('Game is locked. Cannot update prediction.');
        }

        $oldValues = [
            'predicted_home_score' => $prediction->predicted_home_score,
            'predicted_away_score' => $prediction->predicted_away_score,
        ];

        $prediction->update([
            'predicted_home_score' => $data['predicted_home_score'],
            'predicted_away_score' => $data['predicted_away_score'],
        ]);

        event(new PredictionUpdated($prediction, $oldValues));

        return $prediction;
    }

    public function getUserPredictions(User $user): Collection
    {
        return $this->model->with(['game.homeTeam', 'game.awayTeam'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPredictionsForGame(Game $game): Collection
    {
        return $this->model->with('user')
            ->where('game_id', $game->id)
            ->get();
    }

    public function getUserPredictionForGame(User $user, Game $game): ?Prediction
    {
        return $this->model->where('user_id', $user->id)
            ->where('game_id', $game->id)
            ->first();
    }

    public function deletePrediction(Prediction $prediction): bool
    {
        $game = $prediction->game;

        // Check if game is locked
        if (now()->isAfter($game->lockout_time)) {
            throw new \Exception('Game is locked. Cannot delete prediction.');
        }

        return $prediction->delete();
    }
}
