<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DossierMedical;

class DossierMedicalFactory extends Factory
{
    public function definition()
    {
        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        
        return [
            
            'patient_id' => Patient::factory(),
            'poids' => $this->faker->numberBetween(40, 120),
            'taille' => $this->faker->numberBetween(150, 200),
            'groupe_sanguin' => $this->faker->randomElement($bloodTypes),
            'allergies' => json_encode($this->faker->randomElements(['Pollen', 'Pénicilline', 'Arachides', 'Crustacés'], 2)),
            'antecedents' => json_encode([$this->faker->sentence, $this->faker->sentence]),
            'traitements' => json_encode([$this->faker->word, $this->faker->word]),
        ];
    }
}