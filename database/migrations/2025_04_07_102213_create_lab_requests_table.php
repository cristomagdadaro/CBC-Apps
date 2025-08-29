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
        Schema::create('request_forms_pivot', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('requester_id')->nullable();
            $table->uuid('form_id')->nullable();
            $table->string('request_status')->nullable(); // Pending, Approved, Rejected
            $table->boolean('agreed_clause_1')->default(false);
            $table->boolean('agreed_clause_2')->default(false);
            $table->boolean('agreed_clause_3')->default(false);
            $table->string('approval_constraint')->nullable();
            $table->string('disapproved_remarks')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_forms_pivot');
    }
};
