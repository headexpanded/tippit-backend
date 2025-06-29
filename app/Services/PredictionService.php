<?php

namespace App\Services;

use App\Events\Prediction\PredictionCreated;
use App\Events\Prediction\PredictionUpdated;
use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use Illuminate\Support\Collection;

class PredictionService extends BaseService
{
    /**
     * @param  Prediction  $model
     */
    public function __construct(Prediction $model)
    {
        parent::__construct($model);
    }

    /**
     * @param  User  $user
     * @param  Game  $game
     * @param  array  $data
     *
     * @return Prediction
     * @throws \Exception
     */
    public function createPrediction(User $user, Game $game, array $data): Prediction
    {
        // Check if the game is locked
        if (now()->isAfter($game->lockout_time)) {
            throw new \Exception('Game is locked. Cannot make predictions.');
        }

        // Check if the prediction already exists
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

    /**
     * @param  Prediction  $prediction
     * @param  array  $data
     *
     * @return Prediction
     * @throws \Exception
     */
    public function updatePrediction(Prediction $prediction, array $data): Prediction
    {
        $game = $prediction->game;

        // Check if the game is locked
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

    /**
     * @param  User  $user
     *
     * @return Collection
     */
    public function getUserPredictions(User $user): Collection
    {
        return $this->model->with(['game.homeTeam', 'game.awayTeam'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * @param  Game  $game
     *
     * @return Collection
     */
    public function getPredictionsForGame(Game $game): Collection
    {
        return $this->model->with('user')
            ->where('game_id', $game->id)
            ->get();
    }

    /**
     * @param  User  $user
     * @param  Game  $game
     *
     * @return Prediction|null
     */
    public function getUserPredictionForGame(User $user, Game $game): ?Prediction
    {
        return $this->model->where('user_id', $user->id)
            ->where('game_id', $game->id)
            ->first();
    }

    /**
     * @param  Prediction  $prediction
     *
     * @return bool
     * @throws \Exception
     */
    public function deletePrediction(Prediction $prediction): bool
    {
        $game = $prediction->game;

        // Check if the game is locked
        if (now()->isAfter($game->lockout_time)) {
            throw new \Exception('Game is locked. Cannot delete prediction.');
        }

        return $prediction->delete();
    }

    /**
     * Create or update a prediction for a user and game
     *
     * @param User $user
     * @param int $gameId
     * @param array $data
     * @return Prediction
     * @throws \Exception
     */
    public function createOrUpdatePrediction(User $user, int $gameId, array $data): Prediction
    {
        $game = Game::findOrFail($gameId);

        // Check if the game is locked
        if ($game->isLocked()) {
            throw new \Exception('Game is locked. Cannot make predictions after lockout time.');
        }

        // Check if round is completed
        if ($game->round && $game->round->is_completed) {
            throw new \Exception('Cannot make predictions for completed rounds.');
        }

        // Check if prediction already exists
        $existingPrediction = $this->getUserPredictionForGame($user, $game);

        if ($existingPrediction) {
            // Update existing prediction
            return $this->updatePrediction($existingPrediction, $data);
        } else {
            // Create new prediction
            return $this->createPrediction($user, $game, $data);
        }
    }
}
