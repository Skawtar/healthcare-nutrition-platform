<?php

namespace Database\Seeders;

use App\Models\MedicalRecord;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MedicalRecordsSeeder extends Seeder
{
    public function run()
    {
        // Temporarily disable constraints
        Schema::disableForeignKeyConstraints();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear existing data
        MedicalRecord::truncate();

        // Create medical records
        MedicalRecord::factory()
            ->count(50)
            ->create();

        // Re-enable constraints
        Schema::enableForeignKeyConstraints();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}