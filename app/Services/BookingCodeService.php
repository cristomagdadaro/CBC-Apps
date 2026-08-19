<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class BookingCodeService
{
    /**
     * Character pool: A-Z and 0-9 (36 chars → 36^4 = 1,679,616 combinations per prefix per year).
     */
    private const CHARSET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    /**
     * Generate a unique booking code for the given table.
     *
     * Format: {PREFIX}{YY}{XXXX}
     * Example: VH264K2R, VN268A1Z, HT26B9XC
     *
     * @param string $prefix  Two-letter type prefix (VH, VN, HT)
     * @param string $table   Database table name to check for uniqueness
     * @param int    $maxTries Maximum number of generation attempts before failing
     *
     * @throws \RuntimeException when a unique code cannot be generated after maxTries
     */
    public static function generate(string $prefix, string $table, int $maxTries = 20): string
    {
        $year = now()->format('y'); // e.g. "26"

        for ($attempt = 1; $attempt <= $maxTries; $attempt++) {
            $suffix = static::randomSuffix(4);
            $code   = strtoupper($prefix) . $year . $suffix;

            $exists = DB::table($table)
                ->where('booking_id', $code)
                ->exists();

            if (!$exists) {
                return $code;
            }
        }

        throw new \RuntimeException(
            "Could not generate a unique booking_id for [{$table}] after {$maxTries} attempts."
        );
    }

    /**
     * Generate a random alphanumeric suffix of the given length (A-Z, 0-9).
     */
    private static function randomSuffix(int $length): string
    {
        $charset = self::CHARSET;
        $max     = strlen($charset) - 1;
        $result  = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= $charset[random_int(0, $max)];
        }

        return $result;
    }
}
