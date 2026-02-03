<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participant_step_states', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('event_id', 4)->index();
            $table->uuid('participant_id')->index();
            $table->uuid('event_subform_id')->index();
            $table->string('status')->default('locked');
            $table->timestamp('completed_at')->nullable();
            $table->string('completion_hash')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'participant_id', 'event_subform_id'], 'participant_step_unique');

            $table->foreign('event_subform_id')
                ->references('id')
                ->on('event_subforms')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_step_states');
    }
};
