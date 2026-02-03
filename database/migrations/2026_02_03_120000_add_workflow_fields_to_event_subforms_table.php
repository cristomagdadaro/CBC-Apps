<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_subforms', function (Blueprint $table) {
            $table->integer('step_order')->nullable()->index()->after('form_type');
            $table->boolean('is_enabled')->default(true)->after('step_order');
            $table->timestamp('open_from')->nullable()->after('is_enabled');
            $table->timestamp('open_to')->nullable()->after('open_from');
            $table->string('step_type')->nullable()->after('form_type');
            $table->json('visibility_rules')->nullable()->after('config');
            $table->json('completion_rules')->nullable()->after('visibility_rules');
        });
    }

    public function down(): void
    {
        Schema::table('event_subforms', function (Blueprint $table) {
            $table->dropColumn([
                'step_order',
                'is_enabled',
                'open_from',
                'open_to',
                'step_type',
                'visibility_rules',
                'completion_rules',
            ]);
        });
    }
};
