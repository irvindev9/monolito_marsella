<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Street;

class StreetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $streets = [
            [
                'name' => 'De Ader',
            ],
            [
                'name' => 'De Descartes',
            ],
            [
                'name' => 'De Braille',
            ],
            [
                'name' => 'De Niepce',
            ],
            [
                'name' => 'Marsella Central',
            ]
        ];

        foreach ($streets as $street) {
            Street::create($street);
        }
    }
}
