<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\DossierMedical;


class DossierMedicalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $patients = Patient::all();
        
        foreach ($patients as $patient) {
            DossierMedical::factory()
                ->for($patient)
                ->create();
        }
    }
}
