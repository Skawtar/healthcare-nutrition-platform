<?php

namespace Database\Factories;

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConsultationFactory extends Factory
{
    protected $model = Consultation::class;

    public function definition()
    {
        return [
            'patient_id' => Patient::factory(),
            'medecin_id' => User::factory(),
            'date_heure' => $this->faker->dateTimeBetween('-1 year', '+1 year'),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'completed']),
            'motif' => $this->faker->sentence,
            'notes' => $this->faker->optional()->paragraph,
        ];
    }
}