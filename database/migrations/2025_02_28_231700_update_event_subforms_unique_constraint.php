<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Update the unique constraint to allow multiple custom forms with different templates.
     * The old constraint was (event_id, form_type) which doesn't work when form_type is 'custom'
     * and multiple custom templates are assigned.
     */
    public function up(): void
    {
        Schema::table('event_subforms', function (Blueprint $table) {
            // Drop the old unique constraint
            $table->dropUnique(['event_id', 'form_type']);
        });

        Schema::table('event_subforms', function (Blueprint $table) {
            // Create a new unique index that includes form_type_template_id
            // This allows: (event_id=1, form_type='registration', template_id=null) - unique
            // And: (event_id=1, form_type='custom', template_id=A) + (event_id=1, form_type='custom', template_id=B) - both allowed
            $table->unique(['event_id', 'form_type', 'form_type_template_id'], 'event_subforms_event_form_template_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_subforms', function (Blueprint $table) {
            $table->dropUnique('event_subforms_event_form_template_unique');
        });

        Schema::table('event_subforms', function (Blueprint $table) {
            $table->unique(['event_id', 'form_type']);
        });
    }
};
