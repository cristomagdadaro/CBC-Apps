<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_sample_inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sample_id')->constrained('research_samples')->cascadeOnDelete();
            $table->string('action', 40);
            $table->string('barcode_value', 64)->nullable();
            $table->longText('qr_payload')->nullable();
            $table->json('context')->nullable();
            $table->foreignUuid('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['sample_id', 'action']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_sample_inventory_logs');
    }
};
