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
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('balance_due_reminder_queued_at')->nullable();
            $table->timestamp('balance_due_reminder_sent_at')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'balance_due_reminder_queued_at',
                'balance_due_reminder_sent_at',
            ]);
        });
    }
};
