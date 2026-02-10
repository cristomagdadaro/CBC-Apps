<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('barcode_prri')->nullable()->after('barcode');
            $table->string('par_no')->nullable()->after('remarks');
            $table->string('condition')->nullable()->after('par_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['barcode_prri', 'par_no', 'condition']);
        });
    }
};
