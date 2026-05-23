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
            $table->index('status');
            $table->index('payment_status');
            $table->index('expires_at');

            $table->index([
                'status',
                'payment_status',
                'expires_at'
            ],'booking_expiration_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['status']);

            $table->dropIndex(['payment_status']);

            $table->dropIndex(['expires_at']);
            
            $table->dropIndex('booking_expiration_index');
        });
    }
};
