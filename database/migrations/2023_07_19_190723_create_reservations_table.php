<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_id')->nullable()->constrained();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->dateTime('reservation_date');
            $table->tinyInteger('is_approved')->default(0);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->tinyInteger('is_signed')->default(0);
            $table->tinyInteger('is_paid')->default(0);
            $table->tinyInteger('is_visible')->default(1);
            $table->string('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('approved_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
