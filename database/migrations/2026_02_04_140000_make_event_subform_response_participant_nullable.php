<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE event_subform_responses MODIFY participant_id CHAR(36) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE event_subform_responses MODIFY participant_id CHAR(36) NOT NULL');
    }
};
