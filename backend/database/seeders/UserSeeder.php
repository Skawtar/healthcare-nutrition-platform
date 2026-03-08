<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'cin' => 'ADMIN001', // <-- Changed to a unique value
            'date_naissance' => '1980-01-01',
            'genre' => 'Homme',
            'email' => 'admin@clinique.com',
            'role' => 'admin',
            'password' => Hash::make('admin123'),
            'telephone' => '0612345678',
            'est_actif' => true,
            'ville' => 'Casablanca',
            'specialite_code' => 'CARDIOLOGIE',
        ]);


        // Create sample doctors using factory
        User::factory()->count(15)->create();

        // Create some inactive doctors
        User::factory()->count(3)->inactive()->create();

        // Create specialists with higher consultation fees
        User::factory()->create([
            'specialite_code' => 'CARDIOLOGIE',
            'tarif_consultation' => 600,
            'name' => 'Dr. Cardiologue',
            'email' => 'cardio@clinique.com'
        ]);

        User::factory()->create([
            'specialite_code' => 'DERMATOLOGIE',
            'tarif_consultation' => 550,
            'name' => 'Dr. Dermatologue',
            'email' => 'derma@clinique.com'
        ]);
        User::create([
            'name' => 'Dr. Ahmed Benali',
            'cin' => 'AB123456',
            'date_naissance' => '1985-05-15',
            'genre' => 'Homme',
            'specialite_code' => 'GENERALISTE',
            'num_licence' => 'MD2023',
            'email' => 'dr.benali@clinique.com',
            'role' => 'medecin',
            'adresse' => '123 Avenue Hassan II, Casablanca',
            'date_inscription' => now()->subYears(2),
            'password' => Hash::make('doctor123'),
            'telephone' => '0612345678',
            'est_actif' => true,
            'diplome' => 'Doctorat en Médecine Générale',
            'adresse_cabinet' => '45 Rue Mohammed V, Casablanca',
            'experience' => '10 ans d\'expérience',
            'ville' => 'Casablanca',
            'horaires_debut' => '08:30:00',
            'horaires_fin' => '18:00:00',
            'jours_travail' => json_encode(['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi']),
            'tarif_consultation' => 300,
            'image_profil' => null
        ]);
    }
}