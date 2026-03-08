<?php
use App\Models\PatientBloodSugar;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_blood_sugars', function (Blueprint $table) {
            $table->id();
             $table->unsignedBigInteger('patient_id');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');

            $table->decimal('value', 5, 2); // Blood sugar value (e.g., 90.5 mg/dL or mmol/L)
            $table->string('measurement_type'); // e.g., 'Fasting', 'Before Meal', 'After Meal', 'Random', 'Bedtime'
            $table->dateTime('measurement_at'); // Date and time the measurement was taken
            $table->text('notes')->nullable(); // Any specific notes for this reading

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_blood_sugars');
    }
};