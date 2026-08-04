<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnel_registrations', function (Blueprint $table) {
            $table->string('registration_type')->default('philrice_employee')->after('is_philrice_employee');
            $table->string('course_program')->nullable()->after('registration_type');
            $table->string('id_photo_path')->nullable()->after('course_program');
            $table->timestamp('id_issued_at')->nullable()->after('personnel_id');
        });

        Schema::table('personnels', function (Blueprint $table) {
            $table->string('registration_type')->nullable()->after('employee_id');
            $table->string('course_program')->nullable()->after('registration_type');
            $table->string('id_photo_path')->nullable()->after('course_program');
            $table->timestamp('id_issued_at')->nullable()->after('id_photo_path');
        });
    }

    public function down(): void
    {
        Schema::table('personnel_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'registration_type',
                'course_program',
                'id_photo_path',
                'id_issued_at',
            ]);
        });

        Schema::table('personnels', function (Blueprint $table) {
            $table->dropColumn([
                'registration_type',
                'course_program',
                'id_photo_path',
                'id_issued_at',
            ]);
        });
    }
};
