<?php

 // * Clase de Conexión a Base de Datos (PDO Singleton)
 // * Provee una única instancia de conexión para evitar sobrecarga y asegurar ACID.


namespace Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    // Carga variables desde el archivo .env si existe
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
                $value = trim($value);
                // Quitar comillas si las contiene
                $value = trim($value, "\"'");
                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }

    // Obtiene la conexión PDO activa o crea una nueva si no existe
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $envPath = dirname(__DIR__) . '/.env';
            self::loadEnv($envPath);

            // Obtener parámetros con valores por defecto seguros
            $host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'database');
            $port = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? '3306');
            $db   = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'tienda_electrodomesticos');
            $user = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'root');
            $pass = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? 'root_password');
            $charset = getenv('DB_CHARSET') ?: ($_ENV['DB_CHARSET'] ?? 'utf8mb4');

            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Solo intentar fallback a Docker/XAMPP local si host por defecto era 'database'
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
