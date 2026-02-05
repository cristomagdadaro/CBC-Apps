<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            // agreed_updates already exists in create_participants_table migration, only add region_address
            if (!Schema::hasColumn('participants', 'region_address')) {
                $table->string('region_address')->nullable()->after('province_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn('agreed_updates');
            $table->dropColumn('region_address');
        });
    }
};
