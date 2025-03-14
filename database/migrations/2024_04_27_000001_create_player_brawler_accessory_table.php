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
        Schema::create('player_brawler_accessory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('player_brawler_id');
            $table->foreign('player_brawler_id')->references('id')->on('player_brawlers');
            $table->unsignedBigInteger('brawler_accessory_id');
            $table->foreign('brawler_accessory_id')->references('id')->on('brawler_accessory');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_brawler_accessory');
    }
};
