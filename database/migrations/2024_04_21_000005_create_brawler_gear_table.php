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
        Schema::create('brawler_gear', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('brawler_id');
            $table->foreign('brawler_id')->references('id')->on('brawlers');
            $table->unsignedBigInteger('gear_id');
            $table->foreign('gear_id')->references('id')->on('gears');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brawler_gear');
    }
};
