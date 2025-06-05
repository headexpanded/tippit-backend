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
        Schema::create('user_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('total_points')->default(0);
            $table->integer('rounds_played')->default(0);
            $table->integer('latest_points')->default(0);
            $table->integer('total_predictions')->default(0);
            $table->integer('correct_predictions')->default(0);
            $table->integer('exact_score_predictions')->default(0);
            $table->integer('current_rank')->nullable();
            $table->integer('best_rank')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_statistics');
    }
};
