<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $configs = [
            [
                'name'=> 'terrace_times_per_season',
                'slug'=> 'ttps',
                'description'=> 'Número de veces que se puede usar la terraza por temporada',
                'setting'=> '1',
            ],
            [
                'name'=> 'season_duration',
                'slug'=> 'sd',
                'description'=> 'Duración de la temporada en meses',
                'setting'=> '3',
            ],
            [
                'name'=> 'is_registration_open',
                'slug'=> 'iro',
                'description'=> 'Indica si el registro de la terraza está abierto',
                'setting'=> '1',
            ]
        ];

        foreach ($configs as $config) {
            \App\Models\Config::create($config);
        }
    }
}
