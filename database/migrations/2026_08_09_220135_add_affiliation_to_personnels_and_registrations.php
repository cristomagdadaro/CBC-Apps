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
        Schema::table('personnels', function (Blueprint $table) {
            $table->string('affiliation')->nullable()->after('course_program');
        });

        Schema::table('personnel_registrations', function (Blueprint $table) {
            $table->string('affiliation')->nullable()->after('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personnels', function (Blueprint $table) {
            $table->dropColumn('affiliation');
        });

        Schema::table('personnel_registrations', function (Blueprint $table) {
            $table->dropColumn('affiliation');
        });
    }
};
