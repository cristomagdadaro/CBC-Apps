<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('transaction_id')
                ->constrained('transactions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignUuid('item_id')
                ->nullable()
                ->constrained('items')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->decimal('quantity', 12, 2)->default(1);
            $table->string('unit')->nullable();
            $table->string('barcode_prri')->nullable();
            $table->string('prri_component_no', 5)->nullable();
            $table->date('expiration')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_components');
    }
};
