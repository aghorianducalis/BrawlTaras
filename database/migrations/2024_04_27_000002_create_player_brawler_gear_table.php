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
        Schema::create('player_brawler_gear', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('player_brawler_id');
            $table->foreign('player_brawler_id')->references('id')->on('player_brawlers');
            $table->unsignedBigInteger('brawler_gear_id');
            $table->foreign('brawler_gear_id')->references('id')->on('brawler_gear');
            $table->unsignedTinyInteger('level');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_brawler_gear');
    }
};
