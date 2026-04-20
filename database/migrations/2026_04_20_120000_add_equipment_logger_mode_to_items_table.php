<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('equipment_logger_mode')
                ->default('excluded')
                ->after('supplier_id');
        });

        DB::table('items')
            ->whereIn('category_id', [4, 7])
            ->update(['equipment_logger_mode' => 'tracked_only']);
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('equipment_logger_mode');
        });
    }
};
