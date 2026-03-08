<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PatientFactory extends Factory
{
    public function definition()
    {
        
        // Initialize service_id as null
        $serviceId = null;
        
     

        return [
            'cin' => $this->faker->unique()->numerify('########'),
            'nom' => $this->faker->lastName,
            'prenom' => $this->faker->firstName,
            'date_naissance' => $this->faker->dateTimeBetween('-70 years', '-18 years'),
            'genre' => $this->faker->randomElement(['H', 'F']),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'telephone' => $this->faker->phoneNumber,
            'adresse' => $this->faker->address,
            'image_profil' => $this->faker->optional()->imageUrl(200, 200, 'people'),
            
           
            'current_service_id' => $serviceId,
        ];
    }

  
}