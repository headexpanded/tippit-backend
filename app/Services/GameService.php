<?php

namespace App\Services;

use App\Events\Game\GameCreated;
use App\Events\Game\GameScoreUpdated;
use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GameService extends BaseService
{
    /**
     * @param  Model  $model
     */
    public function __construct(Game $model)
    {
        parent::__construct($model);
    }

    /**
     * @param  array  $data
     *
     * @return Game
     */
    public function createGame(array $data): Game
    {
        $game = $this->model->create($data);
        event(new GameCreated($game));
        return $game;
    }

    /**
     * @param  Game  $game
     * @param  array  $data
     *
     * @return Game
     */
    public function updateGame(Game $game, array $data): Game
    {
        $game->update($data);
        return $game;
    }

    /**
     * @param  Game  $game
     * @param  int  $homeScore
     * @param  int  $awayScore
     *
     * @return Game
     */
    public function updateScore(Game $game, int $homeScore, int $awayScore): Game
    {
        $oldHomeScore = $game->home_score;
        $oldAwayScore = $game->away_score;

        $game->update([
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'status' => 'completed'
        ]);

        // Update predictions and user statistics
        $this->processPredictions($game);

        // Dispatch score update event
        event(new GameScoreUpdated($game, $oldHomeScore, $oldAwayScore));

        return $game;
    }

    /**
     * @return Collection
     */
    public function getUpcomingGames(): Collection
    {
        return $this->model->with(['homeTeam', 'awayTeam'])
            ->where('status', 'scheduled')
            ->where('match_date', '>=', now()->toDateString())
            ->orderBy('match_date')
            ->orderBy('match_time')
            ->get();
    }

    /**
     * @return Collection
     */
    public function getCompletedGames(): Collection
    {
        return $this->model->with(['homeTeam', 'awayTeam'])
            ->where('status', 'completed')
            ->orderBy('match_date', 'desc')
            ->orderBy('match_time', 'desc')
            ->get();
    }

    /**
     * @param  Game  $game
     *
     * @return void
     */
    protected function processPredictions(Game $game): void
    {
        $predictions = Prediction::where('game_id', $game->id)->get();

        foreach ($predictions as $prediction) {
            $points = $this->calculatePoints($prediction, $game);
            $prediction->update(['points' => $points]);

            // Update user statistics
            $this->updateUserStatistics($prediction->user, $points);
        }
    }

    /**
     * @param  Prediction  $prediction
     * @param  Game  $game
     *
     * @return int
     */
    protected function calculatePoints(Prediction $prediction, Game $game): int
    {
        // Exact score prediction
        if ($prediction->predicted_home_score === $game->home_score &&
            $prediction->predicted_away_score === $game->away_score) {
            return 3;
        }

        // Correct result prediction
        $predictedResult = $this->getResult($prediction->predicted_home_score, $prediction->predicted_away_score);
        $actualResult = $this->getResult($game->home_score, $game->away_score);

        if ($predictedResult === $actualResult) {
            return 1;
        }

        return 0;
    }

    /**
     * @param  int  $homeScore
     * @param  int  $awayScore
     *
     * @return string
     */
    protected function getResult(int $homeScore, int $awayScore): string
    {
        if ($homeScore > $awayScore) {
            return 'home_win';
        }
        if ($homeScore < $awayScore) {
            return 'away_win';
        }
        return 'draw';
    }

    /**
     * @param  User  $user
     * @param  int  $points
     *
     * @return void
     */
    protected function updateUserStatistics(User $user, int $points): void
    {
        $statistics = $user->statistics()->firstOrCreate();

        $statistics->total_points += $points;
        $statistics->total_predictions += 1;

        if ($points > 0) {
            $statistics->correct_predictions += 1;
        }

        // Update rank
        $statistics->current_rank = User::whereHas('statistics', function ($query) use ($statistics) {
            $query->where('total_points', '>', $statistics->total_points);
        })->count() + 1;

        $statistics->save();
    }
}
