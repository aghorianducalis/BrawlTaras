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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ext_id')->unique();
            $table->string('tag', 20)->unique();
            $table->string('name');
            $table->string('name_color', 20);
            $table->unsignedInteger('icon_id')->nullable();
            $table->unsignedInteger('trophies')->default(0);
            $table->unsignedInteger('highest_trophies')->default(0);
            $table->unsignedInteger('highest_power_play_points')->default(0);
            $table->unsignedInteger('exp_level')->default(0);
            $table->unsignedInteger('exp_points')->default(0);
            $table->boolean('is_qualified_from_championship_league')->default(false);
            $table->unsignedInteger('solo_victories')->default(0);
            $table->unsignedInteger('duo_victories')->default(0);
            $table->unsignedInteger('trio_victories')->default(0);
            $table->unsignedInteger('best_time_robo_rumble')->default(0);
            $table->unsignedInteger('best_time_as_big_brawler')->default(0);
            $table->unsignedBigInteger('club_id');
            $table->foreign('club_id')->references('id')->on('clubs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
