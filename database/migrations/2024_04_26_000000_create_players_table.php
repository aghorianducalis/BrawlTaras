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
            $table->string('tag', 20)->unique();
            $table->string('name');
            $table->string('name_color', 20);
            $table->unsignedInteger('icon_id');
            $table->unsignedInteger('trophies');
            $table->unsignedInteger('highest_trophies')->nullable();
            $table->unsignedInteger('highest_power_play_points')->nullable();
            $table->unsignedInteger('exp_level')->nullable();
            $table->unsignedInteger('exp_points')->nullable();
            $table->boolean('is_qualified_from_championship_league')->nullable();
            $table->unsignedInteger('solo_victories')->nullable();
            $table->unsignedInteger('duo_victories')->nullable();
            $table->unsignedInteger('trio_victories')->nullable();
            $table->unsignedInteger('best_time_robo_rumble')->nullable();
            $table->unsignedInteger('best_time_as_big_brawler')->nullable();

            $table->unsignedBigInteger('club_id')->nullable();
            $table->foreign('club_id')->references('id')->on('clubs');
            $table->string('club_role', 100)->nullable();

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
