<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('participants', 'agreed_updates')) 
            Schema::table('participants', function (Blueprint $table) {
                $table->boolean('agreed_updates')->default(false)->after('agreed_tc');
            });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn('agreed_updates');
        });
    }
};
