<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create status reference table FIRST
        Schema::create('regime_statuts', function (Blueprint $table) {
            $table->string('code', 20)->primary();
            $table->string('name');
            $table->string('color');
            $table->timestamps();
        });

        // Then create the main diet plan table
        Schema::create('regime_alimentaires', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->unsignedBigInteger('patient_id');
            $table->foreignId('medecin_id')->constrained('users');
            $table->date('date_prescription');
            $table->date('date_expiration');
            $table->integer('calories_journalieres')->nullable();
            $table->json('restrictions')->nullable();
            $table->text('recommandations')->nullable();
            $table->string('statut_code', 20)->default('ACTIF');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('patient_id')
                  ->references('id')
                  ->on('patients')
                  ->onDelete('cascade');
                  
            $table->foreign('statut_code')
                  ->references('code')
                  ->on('regime_statuts');
        });

        // Seed default statuses
        DB::table('regime_statuts')->insert([
            ['code' => 'ACTIF', 'name' => 'Actif', 'color' => 'green'],
            ['code' => 'EXPIRE', 'name' => 'Expiré', 'color' => 'red'],
            ['code' => 'ANNULE', 'name' => 'Annulé', 'color' => 'orange'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('regime_alimentaires');
        Schema::dropIfExists('regime_statuts');
    }
};