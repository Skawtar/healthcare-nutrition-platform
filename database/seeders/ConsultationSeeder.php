<?php

namespace Database\Seeders;

use App\Models\Consultation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConsultationSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        Consultation::truncate();
        
        // Create consultations first
        Consultation::factory()
            ->count(50)
            ->create();
            
        Schema::enableForeignKeyConstraints();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}