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
        Schema::create('trip_plans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('conversation_id')
                ->nullable()
                ->constrained('ai_conversations')
                ->nullOnDelete();

            $table->string('title');

            $table->string('country', 2)->nullable();

            $table->string('city')->nullable();

            $table->unsignedInteger('days')->nullable();

            $table->decimal('budget', 12, 2)->nullable();

            $table->string('travel_style')->nullable();

            $table->json('interests')->nullable();

            $table->date('start_date')->nullable();

            $table->date('end_date')->nullable();

            $table->unsignedInteger('nights_count')->default(1);

            $table->longText('plan');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_plans');
    }
};
