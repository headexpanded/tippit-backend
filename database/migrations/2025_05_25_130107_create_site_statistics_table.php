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
        Schema::create('site_statistics', function (Blueprint $table) {
            $table->id();
            $table->integer('total_users')->default(0);
            $table->integer('active_users')->default(0);
            $table->integer('total_predictions')->default(0);
            $table->integer('total_games')->default(0);
            $table->integer('total_leagues')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_statistics');
    }
};
