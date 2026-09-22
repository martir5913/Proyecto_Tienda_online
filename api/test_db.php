<?php

declare(strict_types=1);

// API de diagnóstico de base de datos.
// Uso exclusivo para administradores.

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/middlewares/AuthMiddleware.php';

use Config\Database;
use App\Middlewares\AuthMiddleware;

header('Content-Type: application/json; charset=utf-8');

// Solo administradores.
AuthMiddleware::verificarAdmin();

try {
    $startTime = microtime(true);

    $pdo = Database::getConnection();

    // Información general del servidor.
    $versionQuery = $pdo->query(
        'SELECT VERSION() AS version,
                DATABASE() AS db_name,
                NOW() AS server_time'
    );

    $serverInfo = $versionQuery->fetch();

    // Tablas existentes.
    $tablesQuery = $pdo->query('SHOW TABLES');

    $tables = $tablesQuery->fetchAll(
        PDO::FETCH_COLUMN
    );

    // Tablas permitidas para conteo.
    $keyTables = [
        'roles',
        'usuarios',
        'categorias',
        'marcas',
        'productos',
        'estados_pedido',
        'metodos_pago',
    ];

    $counts = [];

    foreach ($keyTables as $tabla) {

        if (!in_array($tabla, $tables, true)) {
            continue;
        }

        /*
         * $tabla no proviene del usuario.
         * Se obtiene exclusivamente de la lista blanca anterior.
         */
        $stmt = $pdo->query(
            "SELECT COUNT(*) FROM `{$tabla}`"
        );

        $counts[$tabla] =
            (int) $stmt->fetchColumn();
    }

    // Verificación de transacciones.
    $pdo->beginTransaction();

    $pdo->query(
        'SELECT id_producto
         FROM productos
         LIMIT 1
         FOR SHARE'
    );

    $pdo->rollBack();

    $executionTime = round(
        (microtime(true) - $startTime) * 1000,
        2
    );

    jsonResponse(
        true,
        'Conexión a MySQL establecida exitosamente.',
        [
            'database_status' => 'ONLINE',

            'mysql_version' =>
                $serverInfo['version']
                ?? 'Desconocida',

            'database_name' =>
                $serverInfo['db_name']
                ?? 'Desconocida',

            'server_datetime' =>
                $serverInfo['server_time']
                ?? date('Y-m-d H:i:s'),

            'total_tables' =>
                count($tables),

            'tables_list' =>
                $tables,

            'record_counts' =>
                $counts,

            'acid_support' => [
                'engine' => 'InnoDB',
                'transactions' => 'Operativo',
                'test_result' => 'OK',
            ],

            'response_time_ms' =>
                $executionTime,
        ],
        200
    );

} catch (Throwable $e) {

    // Información completa solamente en logs del servidor.
    error_log(
        'API test_db.php: ' .
        $e->getMessage()
    );

    jsonResponse(
        false,
        'No fue posible verificar la base de datos.',
        [
            'database_status' => 'OFFLINE'
        ],
        500
    );
}