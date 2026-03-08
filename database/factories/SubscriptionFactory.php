<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\Service;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition()
    {
        $startDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $endDate = $this->faker->dateTimeBetween($startDate, '+1 year');

        return [
            'patient_id' => Patient::factory(),
            'service_id' => Service::factory(),
            'status' => $this->faker->randomElement(['active', 'canceled', 'expired']),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'payment_method' => $this->faker->randomElement([
                'credit_card', 
                'paypal', 
                'bank_transfer',
                'apple_pay'
            ]),
        ];
    }

    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
                'start_date' => now()->subMonth(),
                'end_date' => now()->addYear(),
            ];
        });
    }

    public function expired()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'expired',
                'start_date' => now()->subYear(),
                'end_date' => now()->subMonth(),
            ];
        });
    }

    public function canceled()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'canceled',
                'end_date' => now()->addDays(rand(1, 30)),
            ];
        });
    }
}