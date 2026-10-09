<?php

declare(strict_types=1);

namespace Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    private static function loadEnv(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim(trim($value), "\"'");
                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }


    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $envPath = dirname(__DIR__) . '/.env';
            self::loadEnv($envPath);

            $host = getenv('DB_HOST') ?: ($_ENV['DB_HOST']);
            $port = getenv('DB_PORT') ?: ($_ENV['DB_PORT']);
            $db   = getenv('DB_NAME') ?: ($_ENV['DB_NAME']);
            $user = getenv('DB_USER') ?: ($_ENV['DB_USER']);
            $pass = getenv('DB_PASS') ?: ($_ENV['DB_PASS']);
            $charset = getenv('DB_CHARSET') ?: ($_ENV['DB_CHARSET']);

            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                if ($host === 'database') {
                    try {
                        $fallbackDsn = "mysql:host=127.0.0.1;port=3308;dbname={$db};charset={$charset}";
                        self::$instance = new PDO($fallbackDsn, $user, $pass, $options);
                        return self::$instance;
                    } catch (PDOException $e2) {
                        throw new PDOException("Error de conexión a la base de datos (Host: {$host}): " . $e->getMessage(), (int)$e->getCode());
                    }
                }
                throw new PDOException("Error de conexión a la base de datos (Host: {$host}, BD: {$db}, Usuario: {$user}): " . $e->getMessage(), (int)$e->getCode());
            }
        }

        return self::$instance;
    }
}
