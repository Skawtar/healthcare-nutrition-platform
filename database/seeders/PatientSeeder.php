<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\Service;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run()
    {
        // First ensure at least one service exists
        if (Service::count() === 0) {
            Service::factory()->count(5)->create();
        }

        // Create regular patients
        Patient::factory()
            ->count(50)
            ->create();

       

    

    }
}