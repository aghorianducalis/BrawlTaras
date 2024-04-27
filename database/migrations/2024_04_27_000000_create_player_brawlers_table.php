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
        Schema::create('player_brawlers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->foreign('player_id')->references('id')->on('players');
            $table->unsignedBigInteger('brawler_id');
            $table->foreign('brawler_id')->references('id')->on('brawlers');
            $table->unsignedSmallInteger('power')->default(0);
            $table->unsignedSmallInteger('rank')->default(0);
            $table->unsignedInteger('trophies')->default(0);
            $table->unsignedInteger('highest_trophies')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_brawlers');
    }
};
