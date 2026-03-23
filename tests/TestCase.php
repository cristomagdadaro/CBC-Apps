<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PDO;
use PDOException;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected static bool $testingDatabaseCreated = false;

    protected function setUpTraits()
    {
        $this->ensureTestingDatabaseExists();

        return parent::setUpTraits();
    }

    protected function ensureTestingDatabaseExists(): void
    {
        if (static::$testingDatabaseCreated) {
            return;
        }

        if (env('DB_CONNECTION') !== 'mysql') {
            return;
        }

        $database = env('DB_DATABASE');

        if (! $database) {
            return;
        }

        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', '');
        $charset = env('DB_CHARSET', 'utf8mb4');
        $collation = env('DB_COLLATION', 'utf8mb4_unicode_ci');

        try {
            $this->createTestingDatabase($host, $port, $username, $password, $charset, $collation, $database);
        } catch (PDOException $exception) {
            throw new RuntimeException('Failed to prepare testing database: ' . $exception->getMessage(), $exception->getCode(), $exception);
        }

        static::$testingDatabaseCreated = true;
    }

    protected function createTestingDatabase(string $host, string $port, string $username, string $password, string $charset, string $collation, string $database): void
    {
        $databaseIdentifier = str_replace('`', '``', $database);
        $dsn = "mysql:host={$host};port={$port};charset={$charset}";

        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$databaseIdentifier}` CHARACTER SET {$charset} COLLATE {$collation}");
    }
}