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
        Schema::create('consultations', function (Blueprint $table) {
            // Reverted to auto-incrementing integer primary key as requested
            $table->id(); // This creates an unsignedBigInteger, auto-incrementing primary key

            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('medecin_id');
            $table->timestamp('date_heure');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled', 'rejected'])
                  ->default('pending');
            $table->text('motif')->nullable();
            $table->text('notes')->nullable();
            $table->text('ordonnance')->nullable();
            $table->timestamps();

            // Foreign keys remain the same as they reference integer IDs
            $table->foreign('patient_id')->references('id')->on('patients');
            $table->foreign('medecin_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
