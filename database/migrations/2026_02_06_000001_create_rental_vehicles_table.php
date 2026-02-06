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
        Schema::create('rental_vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('vehicle_type', ['innova', 'pickup', 'van', 'suv']);
            $table->date('date_from');
            $table->date('date_to');
            $table->time('time_from');
            $table->time('time_to');
            $table->string('purpose');
            $table->string('requested_by');
            $table->string('contact_number');
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for conflict detection queries
            $table->index(['vehicle_type', 'status', 'date_from', 'date_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_vehicles');
    }
};
