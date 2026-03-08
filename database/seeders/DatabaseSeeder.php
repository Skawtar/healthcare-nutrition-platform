<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       $this->call([
            UserSeeder::class,
            ServiceSeeder::class, // Must run first
            PatientSeeder::class,
         ConsultationSeeder::class,
            DossierMedicalSeeder::class,
            PatientBloodPressureSeeder::class, // Depends on Patient
            PatientBloodSugarSeeder::class, 
            MedicalRecordsSeeder::class,
            DocumentMedicalSeeder::class,
            RegimeAlimentaireSeeder::class,
            SubscriptionSeeder::class, // Depends on Patient and Service

            


        



        ]);
    }
}
