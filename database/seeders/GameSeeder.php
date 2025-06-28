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

        // Generate fixtures for each team to play each other twice
        $fixtures = $this->generateFixtures($teams);

        foreach ($rounds as $roundIndex => $round) {
            $roundFixtures = $fixtures[$roundIndex] ?? [];

            foreach ($roundFixtures as $fixture) {
                $homeTeam = $teams->find($fixture['home']);
                $awayTeam = $teams->find($fixture['away']);

                // Schedule game within round period (Thursday 18:00 to Sunday 19:00)
                $gameDate = $this->getGameDateWithinRound($round);
                $gameTime = $this->getGameTime();
                $lockoutTime = Carbon::parse($gameDate . ' ' . $gameTime)->subMinutes(10);

                // Generate results for completed rounds
                $homeScore = null;
                $awayScore = null;
                $status = 'scheduled';

                if ($round->is_completed) {
                    $homeScore = $this->generateRealisticScore($homeTeam, $awayTeam, true);
                    $awayScore = $this->generateRealisticScore($awayTeam, $homeTeam, false);
                    $status = 'completed';
                }

                Game::create([
                    'round_id' => $round->id,
                    'home_team_id' => $homeTeam->id,
                    'away_team_id' => $awayTeam->id,
                    'game_date' => $gameDate,
                    'game_time' => $gameTime,
                    'home_score' => $homeScore,
                    'away_score' => $awayScore,
                    'status' => $status,
                    'lockout_time' => $lockoutTime,
                    'season' => '2025/26'
                ]);
            }
        }
    }

    /**
     * Generate fixtures for a round-robin tournament (each team plays each other twice)
     */
    private function generateFixtures($teams): array
    {
        $teamIds = $teams->pluck('id')->toArray();
        $numTeams = count($teamIds);
        $fixtures = [];

        // Generate first half of season (each team plays each other once)
        for ($round = 0; $round < $numTeams - 1; $round++) {
            $roundFixtures = [];

            for ($i = 0; $i < $numTeams / 2; $i++) {
                $home = $teamIds[$i];
                $away = $teamIds[$numTeams - 1 - $i];

                if ($home != $away) {
                    $roundFixtures[] = ['home' => $home, 'away' => $away];
                }
            }

            $fixtures[] = $roundFixtures;

            // Rotate teams for next round (keep first team fixed, rotate others)
            $lastTeam = array_pop($teamIds);
            array_splice($teamIds, 1, 0, $lastTeam);
        }

        // Generate second half of season (reverse fixtures)
        for ($round = 0; $round < $numTeams - 1; $round++) {
            $roundFixtures = [];

            for ($i = 0; $i < $numTeams / 2; $i++) {
                $home = $teamIds[$i];
                $away = $teamIds[$numTeams - 1 - $i];

                if ($home != $away) {
                    $roundFixtures[] = ['home' => $away, 'away' => $home]; // Reverse
                }
            }

            $fixtures[] = $roundFixtures;

            // Rotate teams for next round
            $lastTeam = array_pop($teamIds);
            array_splice($teamIds, 1, 0, $lastTeam);
        }

        return $fixtures;
    }

    /**
     * Get a random game date within the round period
     */
    private function getGameDateWithinRound($round): string
    {
        $startDate = Carbon::parse($round->start_date);
        $endDate = Carbon::parse($round->end_date);

        // Random day between Thursday and Sunday
        $daysDiff = $startDate->diffInDays($endDate);
        $randomDays = rand(0, $daysDiff);

        return $startDate->addDays($randomDays)->format('Y-m-d');
    }

    /**
     * Get a random game time (evening games)
     */
    private function getGameTime(): string
    {
        $times = ['15:00:00', '17:00:00', '18:30:00'];
        return $times[array_rand($times)];
    }

    /**
     * Generate realistic scores with some surprises
     */
    private function generateRealisticScore($team, $opponent, $isHome): int
    {
        // Base scoring probability
        $baseGoals = rand(0, 3);

        // Home advantage (slight boost)
        if ($isHome) {
            $baseGoals += rand(0, 1);
        }

        // Some surprise results (20% chance of high scoring)
        if (rand(1, 100) <= 20) {
            $baseGoals += rand(1, 2);
        }

        // Some surprise results (10% chance of very low scoring)
        if (rand(1, 100) <= 10) {
            $baseGoals = max(0, $baseGoals - rand(1, 2));
        }

        return min(5, $baseGoals); // Cap at 5 goals
    }
}
