<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\RegimeAlimentaire;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegimeAlimentaireSeeder extends Seeder
{
    public function run()
    {
        // First ensure we have the basic statuses in regime_statuts table
        if (DB::table('regime_statuts')->count() == 0) {
            DB::table('regime_statuts')->insert([
                ['code' => 'ACTIF', 'name' => 'Actif', 'color' => 'green'],
                ['code' => 'EXPIRE', 'name' => 'Expiré', 'color' => 'red'],
                ['code' => 'ANNULE', 'name' => 'Annulé', 'color' => 'orange'],
            ]);
        }

        // Create test data if needed
        if (Patient::count() == 0) {
            Patient::factory()->count(5)->create();
        }
        
        if (User::where('role', 'medecin')->count() == 0) {
            User::factory()->count(3)->create(['role' => 'medecin']);
        }

        // Create diet plans with proper JSON data
        RegimeAlimentaire::factory()->count(10)->create([
            'restrictions' => function() {
                $options = [
                    ['sugar'],
                    ['salt'],
                    ['gluten'],
                    ['dairy'],
                    ['sugar', 'salt'],
                    ['gluten', 'dairy'],
                    null
                ];
                return $options[array_rand($options)];
            },
            'statut_code' => 'ACTIF' // Default status
        ]);
    }
}