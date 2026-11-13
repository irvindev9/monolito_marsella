<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_verification_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code', 4);
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        $userIdsWithReservations = DB::table('reservations')
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->unique()
            ->all();

        DB::table('users')
            ->whereNull('email_verified_at')
            ->where(function ($query) use ($userIdsWithReservations) {
                $query->where('role_id', 1);
                if (! empty($userIdsWithReservations)) {
                    $query->orWhereIn('id', $userIdsWithReservations);
                }
            })
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        Schema::dropIfExists('email_verification_codes');
    }
};
