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
        Schema::create('city_travel_category', function (Blueprint $table) {
            
            $table->foreignId('city_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('travel_category_id')
                ->constrained()
                ->cascadeOnDelete();

            //composite primary key(city_id+travel_category_id)
            $table->primary([
                'city_id',
                'travel_category_id'
            ]);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('city_travel_category');
    }
};
