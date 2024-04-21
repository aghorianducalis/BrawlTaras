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
        Schema::create('brawler_star_power', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('brawler_id');
            $table->foreign('brawler_id')->references('id')->on('brawlers');
            $table->unsignedBigInteger('star_power_id');
            $table->foreign('star_power_id')->references('id')->on('star_powers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brawler_star_power');
    }
};
