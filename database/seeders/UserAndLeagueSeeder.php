<?php

namespace Database\Seeders;

use App\Models\Round;
use App\Models\RoundUserStatistics;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserAndLeagueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 35 users
        $users = [];
        for ($i = 1; $i <= 35; $i++) {
            $users[] = [
                'username' => fake('en_GB')->firstName,
                'email' => fake()->unique()->safeEmail(),
                'password' => Hash::make('password'),
                'supported_team_id' => rand(1, 12),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('users')->insert($users);

        // Create 3 leagues
        $leagues = [
            [
                'name' => 'The Champions League',
                'description' => 'For the best of the best',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'The Rookies',
                'description' => 'New players welcome',
                'created_by' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'The Veterans',
                'description' => 'Experience counts',
                'created_by' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('leagues')->insert($leagues);

        // Add 8 users to each league
        $leagueUsers = [];
        for ($leagueId = 1; $leagueId <= 3; $leagueId++) {
            $startUser = ($leagueId - 1) * 8 + 1;
            for ($i = 0; $i < 8; $i++) {
                $leagueUsers[] = [
                    'league_id' => $leagueId,
                    'user_id' => $startUser + $i,
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        DB::table('league_user')->insert($leagueUsers);
        // Create user statistics for all users
        $userStats = [];
        for ($i = 1; $i <= 35; $i++) {
            $userStats[] = [
                'user_id' => $i,
                'total_points' => $i + 2,
                'rounds_played' => $i % 10 + 1,
                'latest_points' => $i % 5,
                'total_predictions' => 0,
                'correct_predictions' => $i % 5,
                'exact_score_predictions' => $i % 3,
                'current_rank' => $i,
                'best_rank' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('user_statistics')->insert($userStats);

        $completedRounds = Round::where('is_completed', true)->get();
        $users = User::all();

        foreach ($completedRounds as $round) {
            $gamesInRound = $round->games;

            foreach ($users as $user) {
                // For each user and completed round, create statistics
                $predictionsCount = rand(0, $gamesInRound->count());
                $correctPredictions = rand(0, $predictionsCount);
                $exactScorePredictions = rand(0, $correctPredictions);
                $pointsEarned = ($correctPredictions * 3) + ($exactScorePredictions * 2);

                RoundUserStatistics::create([
                    'user_id' => $user->id,
                    'round_id' => $round->id,
                    'points_earned' => $pointsEarned,
                    'predictions_made' => $predictionsCount,
                    'correct_predictions' => $correctPredictions,
                    'exact_score_predictions' => $exactScorePredictions,
                ]);
            }
        }




        // Create league rankings for all leagues
        $leagueRankings = [];
        for ($i = 1; $i <= 3; $i++) {
            $leagueRankings[] = [
                'league_id' => $i,
                'total_points' => 0,
                'average_points' => 0,
                'member_count' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('league_rankings')->insert($leagueRankings);
    }
}
