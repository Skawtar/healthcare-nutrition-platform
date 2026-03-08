<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Patient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear existing data
        Patient::query()->update(['current_service_id' => null]);
        Service::truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $services = [
            [
                'name' => 'Basic',
                'price' => 9.99,
                'billing_period' => 'monthly',
                'description' => 'Basic health monitoring features'
            ],
            [
                'name' => 'Standard',
                'price' => 19.99,
                'billing_period' => 'monthly',
                'description' => 'Standard health features'
            ],
            [
                'name' => 'Premium',
                'price' => 29.99,
                'billing_period' => 'monthly',
                'description' => 'Premium health features'
            ],
            [
                'name' => 'Annual Basic',
                'price' => 99.99,
                'billing_period' => 'yearly',
                'description' => 'Annual basic plan'
            ],
            [
                'name' => 'Annual Premium',
                'price' => 299.99,
                'billing_period' => 'yearly',
                'description' => 'Annual premium plan'
            ]
        ];

        foreach ($services as $service) {
            Service::create([
                'name' => $service['name'],
                'slug' => Str::slug($service['name']),
                'description' => $service['description'],
                'price' => $service['price'],
                'billing_period' => $service['billing_period'],
                'features' => [
                    'feature1' => '24/7 Support',
                    'feature2' => 'Health Tracking',
                    'feature3' => 'Medical Reports'
                ],
                'is_active' => true
            ]);
        }
    }
}