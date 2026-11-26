<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('configs')->where('slug', 'payment_fee')->exists()) {
            $now = now();
            DB::table('configs')->insert([
                'name' => 'payment_fee',
                'description' => 'Cuota de reservación',
                'slug' => 'payment_fee',
                'setting' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('configs')->where('slug', 'payment_fee')->delete();
    }
};
