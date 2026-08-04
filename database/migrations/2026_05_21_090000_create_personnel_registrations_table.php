<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnel_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('fname');
            $table->string('mname')->nullable();
            $table->string('lname');
            $table->string('suffix')->nullable();
            $table->string('position');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('email');
            $table->string('employee_id')->nullable();
            $table->boolean('is_philrice_employee')->default(true);
            $table->string('status')->default('pending');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('verification_sent_at')->nullable();
            $table->text('rejection_remarks')->nullable();
            $table->string('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('personnel_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'email_verified_at']);
            $table->index('email');
            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnel_registrations');
    }
};
