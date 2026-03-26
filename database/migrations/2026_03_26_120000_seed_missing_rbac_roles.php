<?php

use App\Enums\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $timestamp = now();

        DB::table('roles')->upsert(
            collect(Role::defaults())
                ->map(fn (array $role) => [
                    ...$role,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ])
                ->all(),
            ['name'],
            ['label', 'description', 'updated_at']
        );
    }

    public function down(): void
    {
        // Intentionally left blank to avoid removing roles that may already be assigned.
    }
};
