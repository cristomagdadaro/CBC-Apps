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
        if (!Schema::hasTable('event_scan_logs')) {
            Schema::create('event_scan_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('event_id', 36)->index();
            $table->uuid('registration_id')->nullable()->index();
            $table->string('scan_type', 36)->index();
            $table->string('status', 36)->index();
            $table->foreignUuid('scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('scanned_at')->index();
            $table->string('payload_hash')->index();
            $table->string('signature')->nullable();
            $table->string('terminal_id')->nullable();
            $table->text('reason')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'registration_id', 'scan_type', 'status'], 'event_scan_log_lookup');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_scan_logs');
    }
};
