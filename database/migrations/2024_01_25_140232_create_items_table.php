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
        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('category_id')->references('id')->on('categories')->NoactionOnDelete()->CascadeOnUpdate();
            $table->foreignId('supplier_id')->references('id')->on('suppliers')->NoactionOnDelete()->CascadeOnUpdate();
            $table->longText('image')->nullable();
            $table->unique(['name', 'brand']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
