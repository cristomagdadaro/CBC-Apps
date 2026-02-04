<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loc_cities', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('city');
            $table->string('province');
            $table->string('region');
            $table->decimal('latitude', 10, 6)->nullable();
            $table->decimal('longitude', 10, 6)->nullable();

            $table->index('region');
            $table->index('province');
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loc_cities');
    }
};
