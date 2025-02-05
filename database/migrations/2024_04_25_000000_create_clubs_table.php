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
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->string('tag', 20)->unique();
            $table->string('name');
            $table->string('description')->nullable();

            $table->string('type', 20)->nullable();
//            $table->unsignedBigInteger('club_type_id')->unique();

            $table->unsignedBigInteger('badge_id')->nullable();
            $table->unsignedBigInteger('required_trophies')->nullable();
            $table->unsignedBigInteger('trophies')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};
