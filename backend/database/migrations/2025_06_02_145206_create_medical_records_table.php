<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id('medical_record_id'); // Primary key for medical_records
            $table->string('patient_cin');

            // These foreign keys are correctly defined as unsignedBigInteger
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade'); // Keep this one
            $table->foreignId('medecin_id')->constrained('users')->onDelete('cascade');

            $table->foreignId('consultation_id')->constrained('consultations')->onDelete('cascade');

            // REMOVE the following two duplicate lines:
            // $table->unsignedBigInteger('patient_id');
            // $table->foreign('patient_id')->references('id')->on('patients');

            $table->date('record_start_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes (good practice)
            $table->index('patient_cin');
            $table->index('patient_id');
            $table->index('medecin_id');
            $table->index('consultation_id'); // Add this index as recommended
        });
    }

    public function down()
    {
        Schema::dropIfExists('medical_records');
    }
};