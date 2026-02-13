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
        if (!Schema::hasTable('audit_logs')) 
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('model_type'); // e.g., 'App\Models\Transaction'
                $table->uuid('model_id');
                $table->enum('action', ['created', 'updated', 'deleted', 'force_deleted']);
                $table->json('old_values')->nullable(); // Previous values before change
                $table->json('new_values')->nullable(); // New values after change
                $table->ipAddress('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();

                // Indexes for efficient querying
                $table->index(['model_type', 'model_id']);
                $table->index('user_id');
                $table->index('action');
                $table->index('created_at');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
