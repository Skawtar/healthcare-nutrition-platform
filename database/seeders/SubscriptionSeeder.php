<?php

namespace Database\Seeders;

use App\Models\Subscription;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{

public function run()
{
    // First ensure we have some services
    $services = \App\Models\Service::all();
    
    if ($services->isEmpty()) {
        \App\Models\Service::factory()
            ->count(3)
            ->create();
        $services = \App\Models\Service::all();
    }

    // Create subscriptions using existing services
    Subscription::factory()
        ->count(20)
        ->create([
            'service_id' => fn() => $services->random()->id
        ]);

    // Active subscriptions
    Subscription::factory()
        ->active()
        ->count(10)
        ->create([
            'service_id' => fn() => $services->random()->id
        ]);

    // Expired subscriptions
    Subscription::factory()
        ->expired()
        ->count(5)
        ->create([
            'service_id' => fn() => $services->random()->id
        ]);

    // Canceled subscriptions
    Subscription::factory()
        ->canceled()
        ->count(5)
        ->create([
            'service_id' => fn() => $services->random()->id
        ]);
}
}