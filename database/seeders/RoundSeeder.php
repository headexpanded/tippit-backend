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
        $rounds = [
            [
                'name' => 'Round 1',
                'start_date' => Carbon::create(2025, 2, 1),
                'end_date' => Carbon::create(2025, 2, 7),
                'is_completed' => true
            ],
            [
                'name' => 'Round 2',
                'start_date' => Carbon::create(2025, 2, 8),
                'end_date' => Carbon::create(2025, 2, 14),
                'is_completed' => true
            ],
            [
                'name' => 'Round 3',
                'start_date' => Carbon::create(2025, 3, 15),
                'end_date' => Carbon::create(2025, 3, 21),
                'is_completed' => true
            ],
            [
                'name' => 'Round 4',
                'start_date' => Carbon::create(2025, 4, 2),
                'end_date' => Carbon::create(2025, 4, 8),
                'is_completed' => true
            ],
            [
                'name' => 'Round 5',
                'start_date' => Carbon::create(2025, 4, 23),
                'end_date' => Carbon::create(2025, 4, 29),
                'is_completed' => true
            ],
            [
                'name' => 'Round 6',
                'start_date' => Carbon::create(2025, 5, 4),
                'end_date' => Carbon::create(2025, 5, 11),
                'is_completed' => true
            ],
            [
                'name' => 'Round 7',
                'start_date' => Carbon::create(2025, 5, 14),
                'end_date' => Carbon::create(2025, 5, 20),
                'is_completed' => true
            ],
            [
                'name' => 'Round 8',
                'start_date' => Carbon::create(2025, 6, 21),
                'end_date' => Carbon::create(2025, 6, 27),
                'is_completed' => false
            ],
            [
                'name' => 'Round 9',
                'start_date' => Carbon::create(2025, 7, 28),
                'end_date' => Carbon::create(2025, 7, 3),
                'is_completed' => false
            ],
            [
                'name' => 'Round 10',
                'start_date' => Carbon::create(2025, 8, 4),
                'end_date' => Carbon::create(2025, 8, 10),
                'is_completed' => false
            ],
            [
                'name' => 'Round 11',
                'start_date' => Carbon::create(2025, 9, 11),
                'end_date' => Carbon::create(2025, 9, 17),
                'is_completed' => false
            ],
            [
                'name' => 'Round 12',
                'start_date' => Carbon::create(2025, 9, 18),
                'end_date' => Carbon::create(2025, 9, 24),
                'is_completed' => false
            ],
        ];

        foreach ($rounds as $round) {
            Round::create($round);
        }
    }
}
