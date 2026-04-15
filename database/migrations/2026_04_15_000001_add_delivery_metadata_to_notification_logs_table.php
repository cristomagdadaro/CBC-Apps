<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->string('delivery_mode', 32)->default('individual')->after('channel');
            $table->uuid('delivery_group_id')->nullable()->after('correlation_id');

            $table->index(['delivery_mode', 'status']);
            $table->index('delivery_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->dropIndex(['delivery_mode', 'status']);
            $table->dropIndex(['delivery_group_id']);
            $table->dropColumn(['delivery_mode', 'delivery_group_id']);
        });
    }
};

