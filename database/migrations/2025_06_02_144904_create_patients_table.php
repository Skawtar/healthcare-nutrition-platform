<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            // Identification
            $table->id();
            $table->string('cin', 20)->unique()->comment('National ID number');
            
            // Personal Information
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->date('date_naissance')->comment('Date of birth');
            $table->enum('genre', ['H', 'F'])->comment('Gender: H=Homme, F=Femme');
            
            // Authentication
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->rememberToken();
            
            // Contact Information
            $table->string('telephone', 20);
            $table->text('adresse');
            
            // Profile Image
            $table->string('image_profil', 255)->nullable()->comment('Path to profile image');
            
          
            // New Service Integration
            $table->foreignId('current_service_id')->nullable()
                  ->constrained('services')
                  ->nullOnDelete()
                  ->comment('Active service reference');
        
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
     
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};