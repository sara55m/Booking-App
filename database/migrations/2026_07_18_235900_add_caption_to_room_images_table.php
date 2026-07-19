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
        Schema::table('room_images', function (Blueprint $table) {
            $table->string('caption')->nullable();
        });
        Schema::table('property_images', function (Blueprint $table) {
            $table->string('caption')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_images', function (Blueprint $table) {
            $table->dropColumn('caption');
        });
        Schema::table('property_images', function (Blueprint $table) {
            $table->dropColumn('caption');
        });
    }
};
