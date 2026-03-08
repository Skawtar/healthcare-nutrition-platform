<?php

namespace Database\Factories;

use App\Models\RegimeAlimentaire;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegimeAlimentaireFactory extends Factory
{
    protected $model = RegimeAlimentaire::class;

    public function definition()
    {
        $restrictionsOptions = [
            ['sugar'],
            ['salt'],
            ['gluten'],
            ['dairy'],
            ['sugar', 'salt'],
            ['gluten', 'dairy'],
            null
        ];

        return [
            'id' => $this->faker->unique()->regexify('[A-Z0-9]{20}'),
            'patient_id' => Patient::factory(),
            'medecin_id' => User::where('role', 'medecin')->inRandomOrder()->first()->id ?? User::factory(),
            'date_prescription' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'date_expiration' => $this->faker->dateTimeBetween('now', '+1 year'),
            'calories_journalieres' => $this->faker->numberBetween(1500, 3000),
            'restrictions' => $this->faker->randomElement($restrictionsOptions),
            'recommandations' => $this->faker->paragraph,
            'statut_code' => $this->faker->randomElement(['ACTIF', 'EXPIRE', 'ANNULE']),
        ];
    }
}