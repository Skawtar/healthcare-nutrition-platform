<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\PatientBloodPressure;
use Faker\Factory as Faker;
use Carbon\Carbon;

class PatientBloodPressureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $patients = Patient::all();

        foreach ($patients as $patient) {
            // Generate data for the last 60 days
            for ($i = 0; $i < 60; $i++) {
                $date = Carbon::now()->subDays($i);

                // For each day, generate 1 to 3 readings
                $numReadings = $faker->numberBetween(1, 3);
                for ($j = 0; $j < $numReadings; $j++) {
                    // Random time within the day
                    $time = $faker->time('H:i:s');
                    $measurementAt = Carbon::parse($date->format('Y-m-d') . ' ' . $time);

                    // Simulate realistic BP values
                    $systolic = $faker->numberBetween(100, 160); // Common range
                    $diastolic = $faker->numberBetween(60, 100);  // Common range

                    // Add some variability to create trends
                    if ($patient->cin == 'AB12345' && $i < 30) { // For a specific patient, simulate higher BP in the last month
                        $systolic = $faker->numberBetween(130, 170);
                        $diastolic = $faker->numberBetween(85, 110);
                    } elseif ($patient->cin == 'CD67890' && $i >= 30) { // Another patient, simulating improvement
                        $systolic = $faker->numberBetween(110, 140);
                        $diastolic = $faker->numberBetween(70, 90);
                    }

                    PatientBloodPressure::create([
                        'patient_id' => $patient->id,
                        'systolic' => $systolic,
                        'diastolic' => $diastolic,
                        'measurement_at' => $measurementAt,
                        'notes' => $faker->boolean(20) ? $faker->sentence(2) : null,
                    ]);
                }
            }
        }
    }
}
