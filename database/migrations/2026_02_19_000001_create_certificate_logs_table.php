<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_logs', function (Blueprint $table) {
            $table->id();
            $table->string('filename')->nullable();
            $table->string('recipient_email')->nullable()->index();
            $table->string('status', 20)->index();
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_logs');
    }
};
