<?php

namespace Database\Factories;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User; // Assuming User model represents medecins
use App\Models\Consultation;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicalRecordFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MedicalRecord::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
       
        $patient = Patient::factory()->create();
        $medecin = User::factory()->create();

        
        $consultation = Consultation::inRandomOrder()->first() ?? Consultation::factory()->create([
             'patient_id' => $patient->id,
             'medecin_id' => $medecin->id,
        ]);


        return [
            // If patient_cin on medical_records should match the patient's CIN:
            'patient_cin' => $patient->cin, // Assuming your Patient model has a 'cin' attribute

            'patient_id' => $patient->id,
            'medecin_id' => $medecin->id,
            'consultation_id' => $consultation->id,
            'record_start_date' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d H:i:s'),
            'notes' => $this->faker->text(300),
        ];
    }
}