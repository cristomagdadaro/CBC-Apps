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
        Schema::create('event_subform_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('form_parent_id')->index();
            $table->uuid('participant_id')->index();
            $table->string('subform_type');
            $table->json('response_data');

            $table->unique(['form_parent_id', 'participant_id']);

            $table->foreign('form_parent_id')
                ->references('id')
                ->on('event_subforms')
                ->cascadeOnDelete();

            $table->foreign('participant_id')
                ->references('id')
                ->on('registrations')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_subform_responses');
    }
};
