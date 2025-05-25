<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserAndMiniLeagueSeeder extends Seeder
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
                'name' => "User $i",
                'email' => "user$i@example.com",
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('users')->insert($users);

        // Create 3 mini leagues
        $miniLeagues = [
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
        DB::table('mini_leagues')->insert($miniLeagues);

        // Add 8 users to each mini league
        $miniLeagueUsers = [];
        for ($leagueId = 1; $leagueId <= 3; $leagueId++) {
            $startUser = ($leagueId - 1) * 8 + 1;
            for ($i = 0; $i < 8; $i++) {
                $miniLeagueUsers[] = [
                    'mini_league_id' => $leagueId,
                    'user_id' => $startUser + $i,
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        DB::table('mini_league_user')->insert($miniLeagueUsers);

        // Create user statistics for all users
        $userStats = [];
        for ($i = 1; $i <= 35; $i++) {
            $userStats[] = [
                'user_id' => $i,
                'total_points' => 0,
                'total_predictions' => 0,
                'correct_predictions' => 0,
                'exact_score_predictions' => 0,
                'current_rank' => null,
                'best_rank' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('user_statistics')->insert($userStats);

        // Create league rankings for all mini leagues
        $leagueRankings = [];
        for ($i = 1; $i <= 3; $i++) {
            $leagueRankings[] = [
                'mini_league_id' => $i,
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
