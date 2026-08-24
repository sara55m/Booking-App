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
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('rejection_reason')->nullable()->after('approved_at');
            $table->text('rejection_note')->nullable()->after('rejection_reason');
            $table->boolean('can_resubmit')->default(false)->after('rejection_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn([
                'rejection_reason',
                'rejection_note',
                'can_resubmit',
            ]);
        });
    }
};
