<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_blood_pressures', function (Blueprint $table) {
            $table->id();
             $table->unsignedBigInteger('patient_id');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->integer('systolic'); // Systolic blood pressure (mmHg)
            $table->integer('diastolic'); // Diastolic blood pressure (mmHg)
            $table->dateTime('measurement_at'); // Date and time the measurement was taken
            $table->text('notes')->nullable(); // Any specific notes for this reading (e.g., "patient felt dizzy")

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_blood_pressures');
    }
};