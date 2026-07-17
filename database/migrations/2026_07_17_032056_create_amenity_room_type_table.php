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
        //drop amenity room table first
        Schema::dropIfExists('amenity_room');
        //then create amenity room type table
        Schema::create('amenity_room_type', function (Blueprint $table) {
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amenity_id')->constrained()->cascadeOnDelete();
        
            $table->primary(['room_type_id', 'amenity_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('amenity_room', function (Blueprint $table) {
            $table->id();
            $table->foreignId('amenity_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::dropIfExists('amenity_room_type');
    }
};
