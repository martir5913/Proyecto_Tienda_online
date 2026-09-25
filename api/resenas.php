<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/models/Model.php';
require_once dirname(__DIR__) . '/app/models/Resena.php';
require_once dirname(__DIR__) . '/app/controllers/ResenaController.php';
require_once dirname(__DIR__) . '/app/middlewares/AuthMiddleware.php';

use App\Controllers\ResenaController;
use App\Middlewares\AuthMiddleware;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$controller = new ResenaController();
$action = (string)($_GET['action'] ?? $_POST['action'] ?? 'producto');
$method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));

if (!in_array($action, ['producto', 'estado', 'crear'], true)) {
    jsonResponse(false, 'Acción no reconocida.', null, 400);
}

// Consulta pública de las reseñas de un producto.
if ($action === 'producto') {
    if ($method !== 'GET') {
        header('Allow: GET');
        jsonResponse(false, 'Método HTTP no permitido.', null, 405);
    }

    $idProducto = filter_input(INPUT_GET, 'id_producto', FILTER_VALIDATE_INT);

    if (!$idProducto || $idProducto <= 0) {
        jsonResponse(false, 'Producto inválido.', null, 422);
    }

    $resultado = $controller->getPorProducto((int)$idProducto);

    jsonResponse(
        (bool)$resultado['success'],
        (string)$resultado['message'],
        $resultado['data'] ?? null,
        (int)$resultado['status']
    );
}

AuthMiddleware::verificarAutenticado();

$idUsuario = filter_var(
    $_SESSION['usuario']['id_usuario'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($idUsuario === false) {
    jsonResponse(false, 'Sesión de usuario inválida.', null, 401);
}

// Consulta si el usuario puede reseñar un producto o ya lo hizo.
if ($action === 'estado') {
    if ($method !== 'GET') {
        header('Allow: GET');
        jsonResponse(false, 'Método HTTP no permitido.', null, 405);
    }

    $idProducto = filter_input(INPUT_GET, 'id_producto', FILTER_VALIDATE_INT);

    if (!$idProducto || $idProducto <= 0) {
        jsonResponse(false, 'Producto inválido.', null, 422);
    }

    $resultado = $controller->getEstadoUsuario((int)$idUsuario, (int)$idProducto);

    jsonResponse(
        (bool)$resultado['success'],
        (string)$resultado['message'],
        $resultado['data'] ?? null,
        (int)$resultado['status']
    );
}

// Crear reseña.
if ($action === 'crear') {
    if ($method !== 'POST') {
        header('Allow: POST');
        jsonResponse(false, 'Método HTTP no permitido.', null, 405);
    }

    $params = $_POST;
    $raw = file_get_contents('php://input');

    if (is_string($raw) && trim($raw) !== '') {
        try {
            $json = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            if (is_array($json)) {
                $params = array_merge($params, $json);
            }
        } catch (\JsonException) {
            jsonResponse(false, 'La solicitud contiene JSON inválido.', null, 400);
        }
    }

    $csrfRecibido = isset($params['csrf_token']) && is_string($params['csrf_token'])
        ? $params['csrf_token']
        : '';

    $csrfSesion = isset($_SESSION['csrf_resena']) && is_string($_SESSION['csrf_resena'])
        ? $_SESSION['csrf_resena']
        : '';

    if (
        $csrfRecibido === '' ||
        $csrfSesion === '' ||
        !hash_equals($csrfSesion, $csrfRecibido)
    ) {
        jsonResponse(
            false,
            'La solicitud no pudo ser validada. Recarga la página e inténtalo nuevamente.',
            null,
            403
        );
    }

    $idProducto = filter_var(
        $params['id_producto'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    $calificacion = filter_var(
        $params['calificacion'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1, 'max_range' => 5]]
    );

    $comentario = isset($params['comentario']) && is_string($params['comentario'])
        ? $params['comentario']
        : '';

    if ($idProducto === false || $calificacion === false) {
        jsonResponse(false, 'Los datos de la reseña no son válidos.', null, 422);
    }

    $resultado = $controller->agregar(
        (int)$idUsuario,
        (int)$idProducto,
        (int)$calificacion,
        $comentario
    );

    jsonResponse(
        (bool)$resultado['success'],
        (string)$resultado['message'],
        $resultado['data'] ?? null,
        (int)$resultado['status']
    );
}
