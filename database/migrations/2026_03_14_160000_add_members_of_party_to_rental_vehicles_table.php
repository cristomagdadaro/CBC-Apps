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
        Schema::table('rental_vehicles', function (Blueprint $table) {
            $table->json('members_of_party')->nullable()->after('requested_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rental_vehicles', function (Blueprint $table) {
            $table->dropColumn('members_of_party');
        });
    }
};
