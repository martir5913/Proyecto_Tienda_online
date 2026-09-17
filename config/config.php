<?php
/**
 * Configuración General del Sistema
 * Define constantes globales, inicializa la sesión y helpers de seguridad.
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de visualización de errores según entorno
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Constantes de rutas base
define('BASE_DIR', dirname(__DIR__));
define('APP_DIR', BASE_DIR . '/app');
define('VIEWS_DIR', BASE_DIR . '/views');
define('PUBLIC_DIR', BASE_DIR . '/public');

// URL Base del proyecto (dinámica)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = str_replace('\\', '/', dirname($scriptName));
// Normalizar ruta para evitar duplicados
if (!str_contains($basePath, 'Proyecto_Tienda_online')) {
    $basePath = '/Proyecto_Tienda_online';
}
define('BASE_URL', rtrim($protocol . $host . $basePath, '/'));

// Roles de usuario del sistema (coinciden con la tabla `roles`)
define('ROL_ADMINISTRADOR', 1);
define('ROL_CLIENTE', 2);

/**
 * Función helper para responder JSON estandarizado en APIs
 */
function jsonResponse(bool $success, string $message, mixed $data = null, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success'   => $success,
        'message'   => $message,
        'data'      => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Helper para verificar si el usuario actual es Administrador
 */
function esAdmin(): bool
{
    return isset($_SESSION['usuario']) && (int)($_SESSION['usuario']['id_rol'] ?? 0) === ROL_ADMINISTRADOR;
}

/**
 * Helper para verificar si hay un usuario autenticado
 */
function estaAutenticado(): bool
{
    return isset($_SESSION['usuario']) && !empty($_SESSION['usuario']['id_usuario']);
}
