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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('code')->nullable()->unique();
            $table->enum('discount_type',['fixed','percentage']);
            $table->decimal('discount_value',10,2);
            $table->decimal('minimum_booking_amount', 10, 2)
            ->nullable();
            $table->integer('minimum_nights')
            ->nullable();
    
            $table->boolean('is_active')
                ->default(true);
        
            $table->timestamp('starts_at')
                ->nullable();
        
            $table->timestamp('ends_at')
                ->nullable();
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
