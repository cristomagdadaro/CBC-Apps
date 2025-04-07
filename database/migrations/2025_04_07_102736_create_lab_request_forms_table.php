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
        Schema::create('lab_request_forms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('request_type')->nullable();
            $table->longText('request_details')->nullable();
            $table->string('request_purpose')->nullable();
            $table->string('project_title')->nullable();
            $table->date('date_of_use')->nullable();
            $table->time('time_of_use')->nullable();
            $table->json('labs_to_use')->nullable();
            $table->json('equipments_to_use')->nullable();
            $table->json('consumables_to_use')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_request_forms');
    }
};
