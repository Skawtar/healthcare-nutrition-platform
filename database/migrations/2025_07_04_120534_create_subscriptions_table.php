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
        Schema::create('subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
    $table->foreignId('service_id')->constrained()->cascadeOnDelete();
    $table->string('status'); // active/canceled/expired
    $table->dateTime('start_date');
    $table->dateTime('end_date');
    $table->string('payment_method')->nullable();
    $table->timestamps();
    
    $table->index(['patient_id', 'status']);
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
