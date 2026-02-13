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
        Schema::create('laboratory_equipment_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('equipment_id')->constrained('items')->cascadeOnDelete();
            $table->unsignedBigInteger('personnel_id')->nullable();
            $table->enum('status', ['active', 'completed', 'overdue', 'cancelled'])->default('active');
            $table->timestamp('started_at');
            $table->timestamp('end_use_at');
            $table->timestamp('actual_end_at')->nullable();
            $table->boolean('active_lock')->default(true);
            $table->text('purpose')->nullable();
            $table->uuid('checked_in_by')->nullable();
            $table->uuid('checked_out_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('personnel_id')->references('id')->on('personnels')->nullOnDelete();
            $table->foreign('checked_in_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('checked_out_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['equipment_id', 'status']);
            $table->index(['status', 'end_use_at']);
            $table->index('equipment_id')->unique()->where('active_lock', true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laboratory_equipment_logs');
    }
};
