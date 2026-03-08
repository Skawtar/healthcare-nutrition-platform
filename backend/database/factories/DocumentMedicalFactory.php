<?php

namespace Database\Factories;

use App\Models\DocumentMedical;
use App\Models\MedicalRecord; // Make sure this is imported
use App\Models\Consultation; // NEW: Import Consultation model
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentMedicalFactory extends Factory
{
    protected $model = DocumentMedical::class;

    public function definition(): array
    {
        // 1. Get a MedicalRecord instance.
        //    It's best practice to either find an existing one or create a new one,
        //    and then extract its ID and the associated patient_id.
        $medicalRecord = MedicalRecord::inRandomOrder()->first();

        if (!$medicalRecord) {
            // Ensure Patient and User factories are run before MedicalRecord factory if they are dependencies
            $medicalRecord = MedicalRecord::factory()->create();
        }

        // 2. Get a Consultation instance.
        //    This is needed for the 'consultation_id' foreign key.
        $consultation = Consultation::inRandomOrder()->first();

        if (!$consultation) {
            // Ensure Patient and User factories are run before Consultation factory if they are dependencies
            $consultation = Consultation::factory()->create();
        }

        return [
            'medical_record_id' => $medicalRecord->medical_record_id, // Use the correct primary key name for MedicalRecord
            'patient_id' => $medicalRecord->patient_id,
            'medecin_id' => $medicalRecord->medecin_id, // Assuming this is the ID of the doctor
            'consultation_id' => $consultation->id, // NEW: Add consultation_id from the fetched Consultation
            
            'date_creation' => $this->faker->dateTimeBetween('-1 year', 'now'),
            // Changed 'fichier' to 'file_path' to match the migration
            // Using a placeholder string for the file path in factory
            'file_path' => 'documents/' . $this->faker->uuid() . '.' . $this->faker->fileExtension(), 
            
            'est_signe' => $this->faker->boolean(70),
            'nom_fichier' => $this->faker->word().'.'.$this->faker->fileExtension(),
            'document_type' => $this->faker->randomElement([
                'ORDONNANCE',
                'BILAN_SANGUIN',
                'RADIOLOGIE',
                'COMPTE_RENDU',
                'AUTRE'
            ]),
        ];
    }
}
