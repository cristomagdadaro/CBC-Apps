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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('po_no')->nullable();
            $table->string('pr_no')->nullable();
            $table->string('serial_no')->nullable();
        });

        // Data Migration from remarks
        $transactions = DB::table('transactions')->whereNotNull('remarks')->get();

        foreach ($transactions as $transaction) {
            $remarks = $transaction->remarks;
            $po = [];
            $pr = [];
            $sn = [];
            $newRemarksParts = [];

            $parts = array_map('trim', explode(';', $remarks));

            foreach ($parts as $part) {
                if (empty($part)) {
                    continue;
                }

                if (preg_match('/^SN:\s*(.+)$/i', $part, $matches)) {
                    $sn[] = trim($matches[1]);
                } elseif (preg_match('/^PO:\s*(.+)$/i', $part, $matches)) {
                    $po[] = trim($matches[1]);
                } elseif (preg_match('/^PR:\s*(.+)$/i', $part, $matches)) {
                    $pr[] = trim($matches[1]);
                } else {
                    $newRemarksParts[] = $part;
                }
            }

            $updates = [];
            if (!empty($po)) {
                $updates['po_no'] = implode(', ', $po);
            }
            if (!empty($pr)) {
                $updates['pr_no'] = implode(', ', $pr);
            }
            if (!empty($sn)) {
                $updates['serial_no'] = implode(', ', $sn);
            }

            if (!empty($updates)) {
                $updates['remarks'] = empty($newRemarksParts) ? null : implode('; ', $newRemarksParts);
                DB::table('transactions')->where('id', $transaction->id)->update($updates);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['po_no', 'pr_no', 'serial_no']);
        });
    }
};
