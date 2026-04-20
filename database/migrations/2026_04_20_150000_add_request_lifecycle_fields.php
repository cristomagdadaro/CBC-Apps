<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requesters', function (Blueprint $table) {
            $table->string('philrice_id')->nullable()->after('affiliation');
        });

        Schema::table('request_forms_pivot', function (Blueprint $table) {
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->string('released_by')->nullable()->after('approved_at');
            $table->timestamp('released_at')->nullable()->after('released_by');
            $table->string('returned_by')->nullable()->after('released_at');
            $table->timestamp('returned_at')->nullable()->after('returned_by');
            $table->timestamp('overdue_notified_at')->nullable()->after('returned_at');
        });
    }

    public function down(): void
    {
        Schema::table('request_forms_pivot', function (Blueprint $table) {
            $table->dropColumn([
                'approved_at',
                'released_by',
                'released_at',
                'returned_by',
                'returned_at',
                'overdue_notified_at',
            ]);
        });

        Schema::table('requesters', function (Blueprint $table) {
            $table->dropColumn('philrice_id');
        });
    }
};
