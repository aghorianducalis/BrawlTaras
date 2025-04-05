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
        Schema::create('battle_results', function (Blueprint $table) {
            $table->id();
            $table->string('mode', 20);
            $table->string('type', 20);
            $table->string('result', 20)->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->integer('trophy_change')->nullable();
            $table->unsignedInteger('rank')->nullable();

            $table->unsignedBigInteger('battle_id');
            $table->foreign('battle_id')->references('id')->on('battles');

            $table->unsignedBigInteger('star_player_id')->nullable();
            $table->foreign('star_player_id')->references('id')->on('battle_players');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battle_results');
    }
};
