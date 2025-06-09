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
        Schema::create('round_user_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('round_id')->constrained()->onDelete('cascade');
            $table->integer('points_earned')->default(0);
            $table->integer('predictions_made')->default(0);
            $table->integer('correct_predictions')->default(0);
            $table->integer('exact_score_predictions')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'round_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('round_user_statistics');
    }
};
