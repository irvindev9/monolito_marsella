<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $rows = [
            [
                'name' => 'payment_bank',
                'description' => 'Banco para transferencias',
                'slug' => 'payment_bank',
                'setting' => 'BBVA',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'payment_clabe',
                'description' => 'CLABE interbancaria',
                'slug' => 'payment_clabe',
                'setting' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'payment_beneficiary',
                'description' => 'Nombre de beneficiario',
                'slug' => 'payment_beneficiary',
                'setting' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($rows as $row) {
            if (! DB::table('configs')->where('slug', $row['slug'])->exists()) {
                DB::table('configs')->insert($row);
            }
        }
    }

    public function down(): void
    {
        DB::table('configs')
            ->whereIn('slug', ['payment_bank', 'payment_clabe', 'payment_beneficiary'])
            ->delete();
    }
};
