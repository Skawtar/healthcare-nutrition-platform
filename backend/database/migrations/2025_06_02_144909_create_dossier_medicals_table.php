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
    {Schema::create('dossier_medicals', function (Blueprint $table) {
    $table->integer('id', 20)->primary()->autoIncrement();
    $table->unsignedBigInteger('patient_id');
    $table->float('poids')->nullable();
    $table->float('taille')->nullable();
    $table->string('groupe_sanguin', 5)->nullable()->checkIn(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', null]);;
    $table->json('allergies')->nullable();
    $table->json('antecedents')->nullable();
    $table->json('traitements')->nullable();
    $table->date('derniere_consultation')->nullable();
    $table->timestamps();

    $table->foreign('patient_id')->references('id')->on('patients');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dossier_medicals');
    }
};
