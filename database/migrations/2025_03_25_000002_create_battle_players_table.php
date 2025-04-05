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
        Schema::create('battle_players', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('player_id');
            $table->foreign('player_id')->references('id')->on('players');

            $table->unsignedBigInteger('battle_team_id')->nullable();
            $table->foreign('battle_team_id')->references('id')->on('battle_teams');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battle_players');
    }
};
