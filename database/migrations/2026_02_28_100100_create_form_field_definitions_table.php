<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_field_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('form_type_template_id')->nullable()->constrained('form_type_templates')->cascadeOnDelete();
            $table->string('field_key');
            $table->string('field_type'); // text, number, email, select, checkbox, textarea, date, time, file, likert, rating, address, rich_text, multiple_choice_grid, linear_scale, section_header, radio
            $table->string('label', 1024);
            $table->string('placeholder', 1024)->nullable();
            $table->text('description')->nullable();
            $table->json('validation_rules')->nullable(); // {required: true, min: 1, max: 255, pattern: null, custom_message: null}
            $table->json('options')->nullable(); // [{value: 'opt1', label: 'Option 1'}, ...] for select/radio/checkbox
            $table->json('display_config')->nullable(); // {width: 'full', css_classes: '', show_if: {field: 'x', operator: 'equals', value: 'y'}}
            $table->json('field_config')->nullable(); // Type-specific config: {min: 1, max: 5} for linear_scale, {rows: [], columns: []} for grid
            $table->integer('sort_order')->default(0);
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->index(['form_type_template_id', 'sort_order']);
            $table->unique(['form_type_template_id', 'field_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_field_definitions');
    }
};
