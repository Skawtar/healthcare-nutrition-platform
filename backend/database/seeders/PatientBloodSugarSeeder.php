<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\PatientBloodSugar; // <-- Add this line
use Faker\Factory as Faker;
use Carbon\Carbon;
class PatientBloodSugarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $patients = Patient::all();

        $measurementTypes = ['Fasting', 'Before Meal', 'After Meal', 'Random', 'Bedtime'];

        foreach ($patients as $patient) {
            // Generate data for the last 60 days
            for ($i = 0; $i < 60; $i++) {
                $date = Carbon::now()->subDays($i);

                // For each day, generate 2 to 5 readings
                $numReadings = $faker->numberBetween(2, 5);
                for ($j = 0; $j < $numReadings; $j++) {
                    $time = $faker->time('H:i:s');
                    $measurementAt = Carbon::parse($date->format('Y-m-d') . ' ' . $time);

                    $type = $faker->randomElement($measurementTypes);
                    $value = 0;

                    // Simulate realistic blood sugar values based on type
                    switch ($type) {
                        case 'Fasting':
                            $value = $faker->randomFloat(1, 70, 130); // 70-130 for fasting
                            break;
                        case 'After Meal':
                            $value = $faker->randomFloat(1, 100, 200); // 100-200 after meal
                            break;
                        case 'Before Meal':
                            $value = $faker->randomFloat(1, 80, 150); // 80-150 before meal
                            break;
                        case 'Bedtime':
                            $value = $faker->randomFloat(1, 90, 160); // 90-160 for bedtime
                            break;
                        case 'Random':
                        default:
                            $value = $faker->randomFloat(1, 70, 250); // Wider range for random
                            break;
                    }

                    // Add some variability for a specific patient to show trends
                    if ($patient->cin == 'CD67890' && $i < 40) { // Patient CD67890 has higher sugars recently
                        $value = $value + $faker->randomFloat(1, 10, 50); // Slightly higher
                    }

                    PatientBloodSugar::create([
                        'patient_id' => $patient->id,
                        'value' => $value,
                        'measurement_type' => $type,
                        'measurement_at' => $measurementAt,
                        'notes' => $faker->boolean(15) ? $faker->sentence(2) : null, // 15% chance of notes
                    ]);
                }
            }
        }
    }
}
