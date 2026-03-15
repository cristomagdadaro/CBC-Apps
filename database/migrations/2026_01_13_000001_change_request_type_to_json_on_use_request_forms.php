<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        // Wrap existing string values into single-element JSON arrays before altering the column type.
        DB::statement("UPDATE use_request_forms SET request_type = JSON_ARRAY(request_type) WHERE request_type IS NOT NULL AND request_type NOT LIKE '[%' ");

        // Change the column type to JSON
        DB::statement('ALTER TABLE use_request_forms MODIFY request_type JSON');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        // Convert JSON arrays back to the first element string
        DB::statement("UPDATE use_request_forms SET request_type = JSON_UNQUOTE(JSON_EXTRACT(request_type, '$[0]')) WHERE JSON_VALID(request_type)");

        // Revert to string column
        DB::statement('ALTER TABLE use_request_forms MODIFY request_type VARCHAR(255)');
    }
};
