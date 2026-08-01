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
        Schema::create('ai_conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_conversation_id')
            ->constrained()
            ->cascadeOnDelete();

            $table->text('content');
            $table->enum('role', ['user', 'assistant']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_conversation_messages');
    }
};
