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
        Schema::create('document_medicals', function (Blueprint $table) {
            $table->id(); // Primary key for document_medicals table itself
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('medecin_id');
            $table->unsignedBigInteger('medical_record_id')->nullable(); // Made nullable, as a document might not always be tied to a specific medical record
            
            // NEW: Add consultation_id as unsignedBigInteger to match consultations.id
            $table->unsignedBigInteger('consultation_id')->nullable(); // Nullable, as a document might not always be tied to a consultation

            $table->timestamp('date_creation')->useCurrent();
            
            // Changed from binary 'fichier' to string 'file_path' for storing file paths (recommended)
            $table->string('file_path')->nullable()->comment('Path to the uploaded file');
            
            $table->boolean('est_signe')->default(false);
            $table->string('nom_fichier')->nullable()->comment('Name of the uploaded file');
            
            $table->enum('document_type', [
                'ORDONNANCE',
                'BILAN_SANGUIN',
                'RADIOLOGIE',
                'COMPTE_RENDU',
                'AUTRE'
            ])->default('AUTRE')->comment('Type of medical document');
            
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('patient_id')->references('id')->on('patients');
            $table->foreign('medecin_id')->references('id')->on('users');
            
            // Foreign key for medical_record_id
            $table->foreign('medical_record_id')->references('medical_record_id')->on('medical_records')
                  ->onDelete('set null'); // Use set null if medical record can be deleted independently
            
            // NEW: Foreign key for consultation_id, referencing unsignedBigInteger id on consultations
            $table->foreign('consultation_id')->references('id')->on('consultations')
                  ->onDelete('set null'); // Set null if a consultation is deleted
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_medicals');
    }
};
