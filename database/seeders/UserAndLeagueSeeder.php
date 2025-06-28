<?php

namespace Database\Seeders;

use App\Models\League;
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
        // Create 500 users
        $users = [];
        $teamIds = range(1, 12);

        for ($i = 1; $i <= 500; $i++) {
            // 10-15% of users don't support any team
            $supportedTeamId = null;
            if (rand(1, 100) <= 85) {
                $supportedTeamId = $teamIds[array_rand($teamIds)];
            }

            $users[] = [
                'username' => fake('en_GB')->firstName . rand(1, 999),
                'email' => fake()->unique()->safeEmail(),
                'password' => Hash::make('password'),
                'supported_team_id' => $supportedTeamId,
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

        // Allocate users to leagues
        $this->allocateUsersToLeagues();
    }

    /**
     * Allocate users to leagues with some users in multiple leagues
     */
    private function allocateUsersToLeagues(): void
    {
        $leagueUsers = [];

        // League 1: 10 users (users 1-10)
        for ($i = 1; $i <= 10; $i++) {
            $leagueUsers[] = [
                'league_id' => 1,
                'user_id' => $i,
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // League 2: 10 users (users 11-20)
        for ($i = 11; $i <= 20; $i++) {
            $leagueUsers[] = [
                'league_id' => 2,
                'user_id' => $i,
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // League 3: 8 users (users 21-28)
        for ($i = 21; $i <= 28; $i++) {
            $leagueUsers[] = [
                'league_id' => 3,
                'user_id' => $i,
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Add some users to multiple leagues
        // User 42 joins League 1 and League 3
        $leagueUsers[] = [
            'league_id' => 1,
            'user_id' => 42,
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
        $leagueUsers[] = [
            'league_id' => 3,
            'user_id' => 42,
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // User 26 joins League 2 and League 3
        $leagueUsers[] = [
            'league_id' => 2,
            'user_id' => 26,
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
        $leagueUsers[] = [
            'league_id' => 3,
            'user_id' => 26,
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('league_user')->insert($leagueUsers);
    }
}
