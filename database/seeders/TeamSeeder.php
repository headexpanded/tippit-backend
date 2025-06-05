<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = [
            [
                'name' => 'F91 Dudelange',
                'short_name' => 'DUD',
                'logo_url' => 'storage/logos/dudelange.svg',
            ],
            [
                'name' => 'FC Swift Hesperange',
                'short_name' => 'SWI',
                'logo_url' => 'storage/logos/hesperange.svg',
            ],
            [
                'name' => 'FC Progrès Niederkorn',
                'short_name' => 'PRO',
                'logo_url' => 'storage/logos/niederkorn.svg',
            ],
            [
                'name' => 'FC UNA Strassen',
                'short_name' => 'STR',
                'logo_url' => 'storage/logos/una-strassen.svg',
            ],
            [
                'name' => 'FC Mondercange',
                'short_name' => 'MON',
                'logo_url' => 'storage/logos/mondercange.svg',
            ],
            [
                'name' => 'FC Wiltz 71',
                'short_name' => 'WIL',
                'logo_url' => 'storage/logos/wiltz.svg',
            ],
            [
                'name' => 'FC Victoria Rosport',
                'short_name' => 'ROS',
                'logo_url' => 'storage/logos/rosport.svg',
            ],
            [
                'name' => 'FC Hostert',
                'short_name' => 'HOS',
                'logo_url' => 'storage/logos/hostert.svg',
            ],
            [
                'name' => 'FC Jeunesse Esch',
                'short_name' => 'JEU',
                'logo_url' => 'storage/logos/jeunesse-esch.svg',
            ],
            [
                'name' => 'FC Etzella Ettelbruck',
                'short_name' => 'ETZ',
                'logo_url' => 'storage/logos/ettelbruck.svg',
            ],
            [
                'name' => 'FC Schifflange 95',
                'short_name' => 'SCH',
                'logo_url' => 'storage/logos/schifflange.svg',
            ],
            [
                'name' => 'FC Rodange 91',
                'short_name' => 'ROD',
                'logo_url' => 'storage/logos/rodange.svg',
            ],
        ];

        foreach ($teams as $team) {
            DB::table('teams')->insert([
                'name' => $team['name'],
                'short_name' => $team['short_name'],
                'logo_url' => $team['logo_url'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
