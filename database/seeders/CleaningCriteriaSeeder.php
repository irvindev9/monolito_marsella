<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CleaningCriteria;

class CleaningCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        CleaningCriteria::firstOrCreate(
            ['name' => 'Limpieza general'],
            ['response_type' => 'boolean', 'order' => 1, 'is_active' => true]
        );
    }
}
