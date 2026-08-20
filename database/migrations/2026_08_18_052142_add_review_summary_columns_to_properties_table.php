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
        Schema::table('properties', function (Blueprint $table) {
            $table->json('ai_review_summary')->nullable();
            $table->timestamp('ai_review_summary_generated_at')->nullable();
            $table->unsignedInteger('ai_review_summary_review_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['ai_review_summary','ai_review_summary_generated_at','ai_review_summary_review_count']);
        });
    }
};
