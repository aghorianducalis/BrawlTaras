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
        Schema::create('star_powers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ext_id')->unique();
            $table->string('name');
            $table->unsignedBigInteger('brawler_id');
            $table->foreign('brawler_id')->references('id')->on('brawlers');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('star_powers');
    }
};
