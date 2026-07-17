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
        Schema::table('rooms', function (Blueprint $table) {

            $table->foreignId('room_type_id')
                ->nullable()
                ->after('property_id')
                ->constrained('room_types')
                ->cascadeOnDelete();
        
            $table->dropColumn([
                'name',
                'capacity',
                'price-per-night',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {

            $table->dropForeign(['room_type_id']);
            $table->dropColumn('room_type_id');

            $table->string('name');
            $table->decimal('price_per_night', 10, 2);
            $table->integer('capacity');
            
        });
    }
};
