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
        Schema::table('offers', function (Blueprint $table) {
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->integer('per_user_limit')->nullable();
            $table->boolean('requires_coupon_code')->default(false);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn(['usage_limit','used_count','per_user_limit','requires_coupon_code']);
        });
    }
};
