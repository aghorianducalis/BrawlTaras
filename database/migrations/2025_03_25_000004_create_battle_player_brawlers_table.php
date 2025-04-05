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
        Schema::create('battle_player_brawlers', function (Blueprint $table) {
            $table->id();

            $table->unsignedSmallInteger('power');
            $table->unsignedInteger('trophies');
            $table->unsignedSmallInteger('trophy_change')->nullable();

            $table->unsignedBigInteger('brawler_id');
            $table->foreign('brawler_id')->references('id')->on('brawlers');

            $table->unsignedBigInteger('battle_player_id');
            $table->foreign('battle_player_id')->references('id')->on('battle_players');

            // this foreign keys are for an easier way to get the corresponding battle and battle result

            $table->unsignedBigInteger('battle_id');
            $table->foreign('battle_id')->references('id')->on('battles');

            $table->unsignedBigInteger('battle_result_id');
            $table->foreign('battle_result_id')->references('id')->on('battle_results');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battle_player_brawlers');
    }
};
