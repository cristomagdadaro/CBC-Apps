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

        if (!Schema::hasColumn('users', 'uuid_tmp')) {
            Schema::table('users', function (Blueprint $table) {
                $table->uuid('uuid_tmp')->nullable()->after('id');
            });
        }

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

        // Convert FK columns to temporary UUID columns but DO NOT add foreign key
        // constraints yet because `users.id` is still the old integer type.
        $this->convertForeignKeyColumn('transactions', 'user_id', true, false, true);
        $this->convertForeignKeyColumn('supp_equip_reports', 'user_id', true, false, true);
        $this->convertForeignKeyColumn('audit_logs', 'user_id', true, false, true);
        $this->convertForeignKeyColumn('registrations', 'checked_in_by', true, false, true);
        $this->convertForeignKeyColumn('event_scan_logs', 'scanned_by', true, false, true);
        $this->convertForeignKeyColumn('laboratory_equipment_logs', 'checked_in_by', true, false, true);
        $this->convertForeignKeyColumn('laboratory_equipment_logs', 'checked_out_by', true, false, true);
        $this->convertForeignKeyColumn('role_user', 'user_id', false, false, false);
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

        // Rename the existing auto-increment PK to a temporary column,
        // remove its AUTO_INCREMENT attribute, then drop the primary key
        // so we can swap in the UUID `id` column safely.
        if (Schema::hasColumn('users', 'id')) {
            // rename id -> id_old
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->renameColumn('id', 'id_old');
                });
            } catch (\Throwable) {
                // ignore if rename fails
            }

            // remove AUTO_INCREMENT from id_old so dropping primary is allowed
            try {
                DB::statement('ALTER TABLE users MODIFY id_old bigint unsigned NOT NULL');
            } catch (\Throwable) {
                // ignore if statement fails
            }

            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropPrimary();
                });
            } catch (\Throwable) {
                // ignore if dropPrimary fails
            }
        }

        // Rename the prepared uuid column into place and set it as primary
        if (Schema::hasColumn('users', 'uuid_tmp')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->renameColumn('uuid_tmp', 'id');
                });
            } catch (\Throwable) {
                // ignore if rename fails
            }

            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->primary('id');
                });
            } catch (\Throwable) {
                // ignore if setting primary fails
            }
        }

        // Optionally drop the old numeric id column if it remains
        if (Schema::hasColumn('users', 'id_old')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropColumn('id_old');
                });
            } catch (\Throwable) {
                // ignore if drop fails
            }
        }

        // Recreate foreign key constraints now that `users.id` is the UUID column.
        $this->addUserForeignKey('transactions', 'user_id', true, true);
        $this->addUserForeignKey('supp_equip_reports', 'user_id', true, true);
        $this->addUserForeignKey('audit_logs', 'user_id', true, true);
        $this->addUserForeignKey('registrations', 'checked_in_by', true, true);
        $this->addUserForeignKey('event_scan_logs', 'scanned_by', true, true);
        $this->addUserForeignKey('laboratory_equipment_logs', 'checked_in_by', true, true);
        $this->addUserForeignKey('laboratory_equipment_logs', 'checked_out_by', true, true);
        $this->addUserForeignKey('role_user', 'user_id', false, false);
        // sessions.user_id intentionally kept without FK in original migration
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

        // Ensure any existing foreign key on the original column is removed
        try {
            Schema::table($tableName, function (Blueprint $table) use ($column) {
                $table->dropForeign([$column]);
            });
        } catch (\Throwable) {
            // ignore if foreign key does not exist or cannot be dropped
        }

        if (!Schema::hasColumn($tableName, "{$column}_tmp")) {
            Schema::table($tableName, function (Blueprint $table) use ($column) {
                $table->uuid("{$column}_tmp")->nullable()->after($column);
            });
        }

        // Use CHAR casting to avoid numeric/string comparison issues during JOIN
        DB::statement("UPDATE {$tableName} t JOIN users u ON CAST(t.{$column} AS CHAR(36)) = CAST(u.id AS CHAR(36)) SET t.{$column}_tmp = u.uuid_tmp");

        // Now safe to drop the original column
        Schema::table($tableName, function (Blueprint $table) use ($column) {
            try {
                $table->dropColumn($column);
            } catch (\Throwable) {
                // ignore if drop fails for any reason
            }
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

    private function addUserForeignKey(string $tableName, string $column, bool $nullable, bool $cascadeOnUpdate): void
    {
        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, $column)) {
            return;
        }

        try {
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
        } catch (\Throwable $e) {
            // ignore if foreign key cannot be created
        }
    }
};
