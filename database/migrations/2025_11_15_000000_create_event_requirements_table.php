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
            $table->string('event_id', 4)->index();
            $table->string('form_type');
            $table->boolean('is_required')->default(true);
            $table->json('config')->nullable();
            $table->dateTime('open_from')->nullable();
            $table->dateTime('open_to')->nullable();
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
