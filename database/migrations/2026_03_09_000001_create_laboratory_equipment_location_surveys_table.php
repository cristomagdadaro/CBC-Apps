<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_equipment_location_surveys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('equipment_id');
            $table->unsignedBigInteger('personnel_id')->nullable();
            $table->string('location_code', 32)->nullable();
            $table->string('location_label', 120);
            $table->timestamp('reported_at')->nullable();
            $table->timestamps();

            $table->unique('equipment_id', 'lab_equipment_location_survey_equipment_unique');
            $table->index('location_code');

            $table->foreign('equipment_id')
                ->references('id')
                ->on('items')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('personnel_id')
                ->references('id')
                ->on('personnels')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_equipment_location_surveys');
    }
};
