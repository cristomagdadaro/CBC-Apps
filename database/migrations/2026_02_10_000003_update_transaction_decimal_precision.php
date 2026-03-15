<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        Schema::table('transactions', function (Blueprint $table) {
            // Modify columns to support larger values (up to 999,999,999.99)
            $table->decimal('unit_price', 15, 2)->nullable()->change();
            $table->decimal('total_cost', 15, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            // Revert to smaller decimal precision
            $table->decimal('unit_price', 8, 2)->nullable()->change();
            $table->decimal('total_cost', 8, 2)->nullable()->change();
        });
    }
};
