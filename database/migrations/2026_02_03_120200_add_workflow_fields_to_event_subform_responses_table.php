<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_subform_responses', function (Blueprint $table) {
            $table->string('status')->default('submitted')->after('response_data');
            $table->string('completion_hash')->nullable()->after('status');
            $table->timestamp('submitted_at')->nullable()->after('completion_hash');
        });
    }

    public function down(): void
    {
        Schema::table('event_subform_responses', function (Blueprint $table) {
            $table->dropColumn(['status', 'completion_hash', 'submitted_at']);
        });
    }
};
