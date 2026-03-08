<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        $medicalSpecialties = [
            'CARDIOLOGIE', 'DERMATOLOGIE', 'PEDIATRIE', 
            'GYNECOLOGIE', 'GENERALISTE', 'RADIOLOGIE',
            'NEUROLOGIE', 'OPHTALMOLOGIE', 'ORTHOPEDIE'
        ];

        $workingDays = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
        $cities = ['Casablanca', 'Rabat', 'Marrakech', 'Fès', 'Tanger'];

        return [
            'name' => $this->faker->name(),
            'cin' => strtoupper(Str::random(1)) . $this->faker->unique()->numberBetween(100000, 999999),
            'date_naissance' => $this->faker->dateTimeBetween('-60 years', '-30 years')->format('Y-m-d'),
            'genre' => Arr::random(['Homme', 'Femme']),
            'specialite_code' => Arr::random($medicalSpecialties),
            'num_licence' => 'MD' . $this->faker->unique()->numberBetween(1000, 9999),
            'email' => $this->faker->unique()->safeEmail(),
            'role' => 'medecin',
            'adresse' => $this->faker->streetAddress(),
            'date_inscription' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'password' => bcrypt('password'), // Default password
            'image_profil' => $this->faker->optional(0.7)->imageUrl(200, 200, 'doctor'),
            'telephone' => '06' . $this->faker->numberBetween(10000000, 99999999),
            'est_actif' => $this->faker->boolean(90),
            'diplome' => $this->faker->randomElement([
                'Doctorat en Médecine',
                'Spécialisation en ' . Arr::random($medicalSpecialties),
                'DES de Médecine Générale'
            ]),
            'adresse_cabinet' => $this->faker->streetAddress(),
            'experience' => $this->faker->numberBetween(1, 30) . ' ans d\'expérience',
            'ville' => Arr::random($cities),
            'horaires_debut' => $this->faker->time('H:i', '08:00'),
            'horaires_fin' => $this->faker->time('H:i', '18:00'),
            'jours_travail' => json_encode($this->faker->randomElements($workingDays, $this->faker->numberBetween(3, 5))),
            'tarif_consultation' => $this->faker->randomElement([200, 250, 300, 350, 400]),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'specialite_code' => null,
            'num_licence' => null,
            'diplome' => null,
            'tarif_consultation' => 0,
            'name' => 'Admin ' . $this->faker->lastName(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'est_actif' => false,
        ]);
    }

    public function generaliste(): static
    {
        return $this->state(fn (array $attributes) => [
            'specialite_code' => 'GENERALISTE',
            'tarif_consultation' => 200,
        ]);
    }

    public function specialiste(): static
    {
        return $this->state(fn (array $attributes) => [
            'tarif_consultation' => $this->faker->numberBetween(400, 600),
        ]);
    }
}