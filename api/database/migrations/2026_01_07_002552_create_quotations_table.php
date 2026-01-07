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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('requested_services')->onDelete('cascade');
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->decimal('price', 10, 2)->nullable();
            $table->text('provider_observations')->nullable();
            $table->enum('status', ['pendiente', 'cotizada', 'rechazada', 'cancelada', 'aceptada'])->default('pendiente');
            // Move rating here as it refers to a specific provider's work
            $table->integer('rating')->nullable();
            $table->text('rating_comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
