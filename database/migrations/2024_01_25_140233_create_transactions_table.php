<?php

use App\Enums\Inventory;
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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->unique()->primary();
            $table->string('barcode')->nullable();
            $table->foreignUuid('item_id')->references('id')->on('items')->NoactionOnDelete()->CascadeOnUpdate();
            $table->enum('transac_type', [
                Inventory::INCOMING->value,
                Inventory::OUTGOING->value,
            ]);
            $table->integer('quantity');
            $table->string('unit')->nullable();
            $table->decimal('unit_price')->nullable();
            $table->decimal('total_cost')->nullable();
            $table->foreignId('personnel_id')->nullable()->references('id')->on('personnels')->NoactionOnDelete()->CascadeOnUpdate();
            $table->string('project_code')->nullable();
            $table->foreignId('user_id')->references('id')->on('users')->NoactionOnDelete()->CascadeOnUpdate();
            $table->date('expiration')->nullable();
            $table->longText('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
