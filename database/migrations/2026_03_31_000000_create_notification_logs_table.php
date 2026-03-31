<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('domain', 120);
            $table->string('event_key', 160);
            $table->string('notifiable_type')->nullable();
            $table->string('notifiable_id')->nullable();
            $table->string('recipient_email');
            $table->string('channel', 32)->default('mail');
            $table->string('status', 32)->default('queued');
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('payload_meta')->nullable();
            $table->uuid('correlation_id')->nullable();
            $table->timestamps();

            $table->index(['domain', 'status']);
            $table->index(['event_key', 'status']);
            $table->index('recipient_email');
            $table->index('correlation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
