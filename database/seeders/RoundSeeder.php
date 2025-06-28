<?php

namespace Database\Seeders;

use App\Models\Round;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RoundSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run(): void
    {
        $rounds = [];

        // Create 22 rounds (enough for each team to play each other twice)
        // First 11 rounds are completed (past), next 11 are future
        for ($i = 1; $i <= 22; $i++) {
            $isCompleted = $i <= 11;

            // Start dates: First round starts Feb 6, 2025 (Thursday)
            $startDate = Carbon::create(2025, 2, 6)->addWeeks($i - 1);
            $endDate = $startDate->copy()->addDays(3); // Thursday to Sunday

            $rounds[] = [
                'name' => "Round {$i}",
                'start_date' => $startDate->setTime(18, 0), // Thursday 18:00
                'end_date' => $endDate->setTime(19, 0), // Sunday 19:00
                'is_completed' => $isCompleted
            ];
        }

        foreach ($rounds as $round) {
            Round::create($round);
        }
    }
}
