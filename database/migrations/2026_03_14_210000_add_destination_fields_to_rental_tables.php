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
            $table->string('destination_location')->nullable()->after('purpose');
            $table->string('destination_city')->nullable()->after('destination_location');
            $table->string('destination_province')->nullable()->after('destination_city');
            $table->string('destination_region')->nullable()->after('destination_province');
        });

        Schema::table('rental_venues', function (Blueprint $table) {
            $table->string('destination_location')->nullable()->after('event_name');
            $table->string('destination_city')->nullable()->after('destination_location');
            $table->string('destination_province')->nullable()->after('destination_city');
            $table->string('destination_region')->nullable()->after('destination_province');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rental_vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'destination_location',
                'destination_city',
                'destination_province',
                'destination_region',
            ]);
        });

        Schema::table('rental_venues', function (Blueprint $table) {
            $table->dropColumn([
                'destination_location',
                'destination_city',
                'destination_province',
                'destination_region',
            ]);
        });
    }
};
