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
        Schema::table('property_policies', function (Blueprint $table) {
            //drop the text column
            $table->dropColumn('cancellation_policy');

            //add cancellation fields
            $table->boolean('free_cancellation')->default(true);

            $table->unsignedSmallInteger('free_cancellation_hours')
                ->nullable();

            $table->unsignedTinyInteger('refund_percentage')
                ->default(100);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_policies', function (Blueprint $table) {

            $table->dropColumn([
                'free_cancellation',
                'free_cancellation_hours',
                'refund_percentage'
            ]);

            $table->text('cancellation_policy');
        });
    }
};
