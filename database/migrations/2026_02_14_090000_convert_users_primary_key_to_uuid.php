<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'id')) {
            return;
        }

        $type = Schema::getColumnType('users', 'id');
        if (!in_array($type, ['bigint', 'integer', 'int'], true)) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid_tmp')->nullable()->after('id');
        });

        DB::table('users')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('users')->where('id', $row->id)->update([
                        'uuid_tmp' => (string) Str::uuid(),
                    ]);
                }
            });

        $this->convertForeignKeyColumn('transactions', 'user_id', true, true, true);
        $this->convertForeignKeyColumn('supp_equip_reports', 'user_id', true, true, true);
        $this->convertForeignKeyColumn('audit_logs', 'user_id', true, true, true);
        $this->convertForeignKeyColumn('registrations', 'checked_in_by', true, true, true);
        $this->convertForeignKeyColumn('event_scan_logs', 'scanned_by', true, true, true);
        $this->convertForeignKeyColumn('laboratory_equipment_logs', 'checked_in_by', true, true, true);
        $this->convertForeignKeyColumn('laboratory_equipment_logs', 'checked_out_by', true, true, true);
        $this->convertForeignKeyColumn('role_user', 'user_id', false, true, false);
        $this->convertForeignKeyColumn('sessions', 'user_id', true, false, false);

        if (Schema::hasTable('personal_access_tokens') && Schema::hasColumn('personal_access_tokens', 'tokenable_id')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->string('tokenable_id_tmp', 36)->nullable()->after('tokenable_id');
            });

            DB::statement("UPDATE personal_access_tokens pat JOIN users u ON pat.tokenable_type = 'App\\\\Models\\\\User' AND pat.tokenable_id = u.id SET pat.tokenable_id_tmp = u.uuid_tmp");
            DB::statement("UPDATE personal_access_tokens SET tokenable_id_tmp = CAST(tokenable_id AS CHAR(36)) WHERE tokenable_id_tmp IS NULL");

            try {
                DB::statement('ALTER TABLE personal_access_tokens DROP INDEX personal_access_tokens_tokenable_type_tokenable_id_index');
            } catch (\Throwable) {
                // ignore if index does not exist
            }

            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->dropColumn('tokenable_id');
            });

            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->renameColumn('tokenable_id_tmp', 'tokenable_id');
            });

            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->index(['tokenable_type', 'tokenable_id']);
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropPrimary();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('uuid_tmp', 'id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->primary('id');
        });
    }

    public function down(): void
    {
        // One-way data migration.
    }

    private function convertForeignKeyColumn(string $tableName, string $column, bool $nullable, bool $hasForeign, bool $cascadeOnUpdate): void
    {
        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, $column)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($column) {
            $table->uuid("{$column}_tmp")->nullable()->after($column);
        });

        DB::statement("UPDATE {$tableName} t JOIN users u ON t.{$column} = u.id SET t.{$column}_tmp = u.uuid_tmp");

        if ($hasForeign) {
            try {
                Schema::table($tableName, function (Blueprint $table) use ($column) {
                    $table->dropForeign([$column]);
                });
            } catch (\Throwable) {
                // ignore if foreign key does not exist
            }
        }

        Schema::table($tableName, function (Blueprint $table) use ($column) {
            $table->dropColumn($column);
        });

        Schema::table($tableName, function (Blueprint $table) use ($column) {
            $table->renameColumn("{$column}_tmp", $column);
        });

        if ($hasForeign) {
            Schema::table($tableName, function (Blueprint $table) use ($column, $nullable, $cascadeOnUpdate) {
                $foreign = $table->foreign($column)->references('id')->on('users');

                if ($nullable) {
                    $foreign->nullOnDelete();
                } else {
                    $foreign->cascadeOnDelete();
                }

                if ($cascadeOnUpdate) {
                    $foreign->cascadeOnUpdate();
                }
            });
        }
    }
};
