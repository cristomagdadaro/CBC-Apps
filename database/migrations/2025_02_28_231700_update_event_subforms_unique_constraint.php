<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
        // Try to drop the old constraint using raw SQL if it exists
        try {
            DB::statement('ALTER TABLE event_subforms DROP INDEX event_subforms_event_id_form_type_unique');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }

        // Only create the new constraint if the form_type_template_id column exists
        if (Schema::hasColumn('event_subforms', 'form_type_template_id')) {
            Schema::table('event_subforms', function (Blueprint $table) {
                // Create a new unique index that includes form_type_template_id
                // This allows: (event_id=1, form_type='registration', template_id=null) - unique
                // And: (event_id=1, form_type='custom', template_id=A) + (event_id=1, form_type='custom', template_id=B) - both allowed
                $table->unique(['event_id', 'form_type', 'form_type_template_id'], 'event_subforms_event_form_template_unique');
            });
        }
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
