<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_requirements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Link back to forms via event_id (string code used across the app)
            $table->string('event_id', 4)->index();
            // Type of requirement: e.g., pre_test, post_test, registration, feedback, custom_*
            $table->string('form_type');
            // Whether this requirement is mandatory for the event
            $table->boolean('is_required')->default(true);
            // Optional JSON config for extra settings per requirement
            $table->json('config')->nullable();
            $table->timestamps();

            $table->foreign('event_id')
                ->references('event_id')
                ->on('forms')
                ->cascadeOnDelete();

            $table->unique(['event_id', 'form_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_requirements');
    }
};
