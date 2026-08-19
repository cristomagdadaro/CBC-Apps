<?php

use App\Services\BookingCodeService;
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
        // ── rental_vehicles ─────────────────────────────────────────────────
        Schema::table('rental_vehicles', function (Blueprint $table) {
            $table->string('booking_id', 8)->nullable()->unique()->after('id');
            $table->string('organization', 255)->nullable()->after('requested_by');
        });

        // Backfill existing vehicle rows
        DB::table('rental_vehicles')->whereNull('booking_id')->orderBy('created_at')->each(function ($row) {
            DB::table('rental_vehicles')
                ->where('id', $row->id)
                ->update(['booking_id' => BookingCodeService::generate('VH', 'rental_vehicles')]);
        });

        // ── rental_venues ────────────────────────────────────────────────────
        Schema::table('rental_venues', function (Blueprint $table) {
            $table->string('booking_id', 8)->nullable()->unique()->after('id');
            $table->string('organization', 255)->nullable()->after('requested_by');
        });

        // Backfill existing venue rows
        DB::table('rental_venues')->whereNull('booking_id')->orderBy('created_at')->each(function ($row) {
            DB::table('rental_venues')
                ->where('id', $row->id)
                ->update(['booking_id' => BookingCodeService::generate('VN', 'rental_venues')]);
        });

        // ── rental_hostels ───────────────────────────────────────────────────
        Schema::table('rental_hostels', function (Blueprint $table) {
            $table->string('booking_id', 8)->nullable()->unique()->after('id');
        });

        // Backfill existing hostel rows
        DB::table('rental_hostels')->whereNull('booking_id')->orderBy('created_at')->each(function ($row) {
            DB::table('rental_hostels')
                ->where('id', $row->id)
                ->update(['booking_id' => BookingCodeService::generate('HT', 'rental_hostels')]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rental_vehicles', function (Blueprint $table) {
            $table->dropUnique(['booking_id']);
            $table->dropColumn(['booking_id', 'organization']);
        });

        Schema::table('rental_venues', function (Blueprint $table) {
            $table->dropUnique(['booking_id']);
            $table->dropColumn(['booking_id', 'organization']);
        });

        Schema::table('rental_hostels', function (Blueprint $table) {
            $table->dropUnique(['booking_id']);
            $table->dropColumn('booking_id');
        });
    }
};
