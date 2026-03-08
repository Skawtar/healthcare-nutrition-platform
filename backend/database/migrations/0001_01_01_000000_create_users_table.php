<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cin', 20)->unique()->comment('National ID or CIN of the doctor');
            $table->date('date_naissance')->nullable();
            $table->enum('genre', ['Homme', 'Femme']);
            $table->string('specialite_code')->nullable();
            $table->string('num_licence')->nullable();
            $table->string('email', 100)->unique();
            // FIX: Removed ->after('email') as it causes a SQL syntax error in Schema::create for some MySQL/MariaDB versions.
            // The order of columns in Schema::create generally doesn't matter for functionality.
            $table->timestamp('email_verified_at')->nullable();
            $table->enum('role', ['medecin', 'admin'])->default('medecin')->comment('Role of the user, either doctor or admin');
            $table->string('adresse', 255)->nullable()->comment('Address of the doctor');
            $table->date('date_inscription')->default(now()->toDateString())->comment('Date when the doctor registered'); // Use toDateString() for date column
            $table->string('password');
            $table->string('image_profil')->nullable()->comment('Path to profile image');
            $table->rememberToken();
            $table->string('telephone', 20)->nullable();
            $table->boolean('est_actif')->default(true);
            $table->string('diplome', 100)->nullable()->comment('Diploma or qualification of the doctor');
            $table->string('adresse_cabinet', 255)->nullable()->comment('Address of the doctor\'s office or clinic');
            $table->string('experience', 255)->nullable()->comment('Years of experience or description of experience');
            $table->string('ville', 50)->nullable()->comment('City where the doctor practices');
            $table->time('horaires_debut')->nullable()->comment('Start time of the doctor\'s working hours');
            $table->time('horaires_fin')->nullable()->comment('End time of the doctor\'s working hours');
            
            $table->json('jours_travail')->nullable()->comment('Days of the week the doctor works (JSON array)');
            $table->decimal('tarif_consultation', 8, 2)->default(0.00)->comment('Consultation fee charged by the doctor');
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
