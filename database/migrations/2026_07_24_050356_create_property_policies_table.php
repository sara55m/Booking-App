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
        Schema::create('property_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete()->unique();//each property has one policy record(one-to-one)

            $table->time('check_in_from');
            $table->time('check_in_until');
            $table->time('check_out_from');
            $table->time('check_out_until');

            $table->boolean('pets_allowed')->default(false);
            $table->boolean('children_allowed')->default(true);
            $table->boolean('smoking_allowed')->default(false);

            $table->unsignedTinyInteger('minimum_check_in_age')->default(18);

            $table->text('cancellation_policy');
            $table->text('important_information')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_policies');
    }
};
