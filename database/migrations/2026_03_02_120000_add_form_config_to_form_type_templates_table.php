<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_type_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('form_type_templates', 'form_config')) {
                $table->json('form_config')->nullable()->after('icon');
            }
        });
    }

    public function down(): void
    {
        Schema::table('form_type_templates', function (Blueprint $table) {
            if (Schema::hasColumn('form_type_templates', 'form_config')) {
                $table->dropColumn('form_config');
            }
        });
    }
};
