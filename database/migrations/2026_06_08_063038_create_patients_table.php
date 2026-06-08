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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_code')->unique();
            $table->unsignedInteger('token_number')->nullable();
            $table->foreignId('doctor_id')->constrained('doctors')->restrictOnDelete();
            $table->string('name');
            $table->string('guardian_name')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('cnic')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('alternate_phone')->nullable();
            $table->string('email')->nullable()->unique();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('blood_group')->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('occupation')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_relation')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('allergies')->nullable();
            $table->text('medical_history')->nullable();
            $table->text('current_medications')->nullable();
            $table->text('chronic_diseases')->nullable();
            $table->string('insurance_provider')->nullable();
            $table->string('insurance_policy_number')->nullable();
            $table->enum('status', ['active', 'inactive', 'deceased'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
