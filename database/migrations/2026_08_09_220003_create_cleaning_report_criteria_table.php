<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cleaning_report_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cleaning_report_id')->constrained();
            $table->foreignId('cleaning_criteria_id')->constrained('cleaning_criteria');
            $table->text('response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cleaning_report_criteria');
    }
};
