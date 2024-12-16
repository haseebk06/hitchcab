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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->text('profile_image_url')->nullable();
            $table->text('car_image_url')->nullable();
            $table->integer('car_seats')->unsigned()->check('car_seats > 0');
            $table->decimal('rating', 3, 2)->nullable()->check('rating >= 0 AND rating <= 5');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
