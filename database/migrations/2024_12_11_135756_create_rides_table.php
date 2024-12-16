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
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->string('origin_address', 255);
            $table->string('destination_address', 255);
            $table->decimal('origin_latitude', 9, 6);
            $table->decimal('origin_longitude', 9, 6);
            $table->decimal('destination_latitude', 9, 6);
            $table->decimal('destination_longitude', 9, 6);
            $table->integer('ride_time')->unsigned();
            $table->decimal('fare_price', 10, 2)->check('fare_price >= 0');
            $table->string('payment_status', 20);
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
