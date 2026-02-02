<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_requirements', function (Blueprint $table) {
            $table->integer('max_slots')->nullable()->after('is_required');
        });

        // remove max_slots from forms table if exists
        if (Schema::hasColumn('forms', 'max_slots')) {
            Schema::table('forms', function (Blueprint $table) {
                $table->dropColumn('max_slots');
            });
        }
    }

    public function down(): void
    {
        Schema::table('event_requirements', function (Blueprint $table) {
            $table->dropColumn('max_slots');
        });
    }
};
