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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ext_id')->unique();
            $table->unsignedBigInteger('map_id');
            $table->foreign('map_id')->references('id')->on('event_maps');
            $table->unsignedBigInteger('mode_id');
            $table->foreign('mode_id')->references('id')->on('event_modes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
