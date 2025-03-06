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
        Schema::create('participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('sex')->nullable();
            $table->string('age')->nullable();
            $table->string('organization')->nullable();
            $table->boolean('is_ip')->default(false);
            $table->boolean('is_pwd')->default(false);
            $table->string('city_address')->nullable();
            $table->string('province_address')->nullable();
            $table->string('country_address')->nullable();
            $table->boolean('agreed_tc')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
