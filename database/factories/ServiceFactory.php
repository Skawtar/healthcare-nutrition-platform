<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

   public function definition()
{
    $name = $this->faker->unique()->word;
    
    return [
        'name' => ucfirst($name),
        'slug' => $name, // Use the same unique word for slug
        'description' => $this->faker->sentence,
        'price' => $this->faker->randomFloat(2, 10, 100),
        'billing_period' => $this->faker->randomElement(['monthly', 'yearly']),
        'features' => json_encode([
            'feature1' => $this->faker->word,
            'feature2' => $this->faker->word,
            'feature3' => $this->faker->word,
        ]),
        'is_active' => true,
    ];
}
}