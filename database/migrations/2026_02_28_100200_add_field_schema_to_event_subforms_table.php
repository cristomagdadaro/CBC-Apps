<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_subforms', function (Blueprint $table) {
            $table->foreignUuid('form_type_template_id')
                ->nullable()
                ->after('form_type')
                ->constrained('form_type_templates')
                ->nullOnDelete();
            $table->json('field_schema')->nullable()->after('config');
        });
    }

    public function down(): void
    {
        Schema::table('event_subforms', function (Blueprint $table) {
            $table->dropForeign(['form_type_template_id']);
            $table->dropColumn(['form_type_template_id', 'field_schema']);
        });
    }
};
