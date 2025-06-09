<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Round;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run(): void
    {
        $rounds = Round::all();
        $teams = Team::all();

        foreach ($rounds as $round) {
            // Create 6 games per round
            for ($i = 0; $i < 6; $i++) {
                $homeTeam = $teams->random();
                $awayTeam = $teams->where('id', '!=', $homeTeam->id)->random();

                $gameDate = Carbon::parse($round->start_date)->addDays(rand(0, 6));
                $gameTime = Carbon::parse($gameDate)->addHours(rand(11, 15));
                $lockoutTime = Carbon::parse($gameTime)->subMinute();

                Game::create([
                    'round_id' => $round->id,
                    'home_team_id' => $homeTeam->id,
                    'away_team_id' => $awayTeam->id,
                    'game_date' => $gameDate,
                    'game_time' => $gameTime->format('H:i:s'),
                    'home_score' => $round->is_completed ? rand(0, 5) : null,
                    'away_score' => $round->is_completed ? rand(0, 5) : null,
                    'status' => $round->is_completed ? 'completed' : 'scheduled',
                    'lockout_time' => $lockoutTime,
                    'is_completed' => $round->is_completed
                ]);
            }
        }
    }
}
