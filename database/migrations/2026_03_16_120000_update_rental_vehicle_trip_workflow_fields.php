<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_vehicles', function (Blueprint $table) {
            $table->string('trip_type')->default('dedicated_trip')->after('vehicle_type');
            $table->json('destination_stops')->nullable()->after('destination_region');
            $table->boolean('is_shared_ride')->default(false)->after('members_of_party');
            $table->string('shared_ride_reference')->nullable()->after('is_shared_ride');
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE rental_vehicles MODIFY vehicle_type VARCHAR(255) NULL");
            DB::statement("ALTER TABLE rental_vehicles MODIFY status VARCHAR(255) NOT NULL DEFAULT 'pending'");
            DB::statement("ALTER TABLE rental_venues MODIFY status VARCHAR(255) NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        Schema::table('rental_vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'trip_type',
                'destination_stops',
                'is_shared_ride',
                'shared_ride_reference',
            ]);
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE rental_vehicles MODIFY vehicle_type VARCHAR(255) NOT NULL");
            DB::statement("ALTER TABLE rental_vehicles MODIFY status VARCHAR(255) NOT NULL DEFAULT 'pending'");
            DB::statement("ALTER TABLE rental_venues MODIFY status VARCHAR(255) NOT NULL DEFAULT 'pending'");
        }
    }
};
