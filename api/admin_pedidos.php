<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/models/Model.php';
require_once dirname(__DIR__) . '/app/models/PedidoAdmin.php';
require_once dirname(__DIR__) . '/app/controllers/PedidoAdminController.php';
require_once dirname(__DIR__) . '/app/middlewares/AuthMiddleware.php';

use App\Controllers\PedidoAdminController;
use App\Middlewares\AuthMiddleware;

header('Content-Type: application/json; charset=utf-8');

AuthMiddleware::verificarAdmin();

$controller = new PedidoAdminController();
$metodo = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$input = json_decode(file_get_contents('php://input'), true);
$input = is_array($input) ? $input : [];
$params = array_merge($_POST, $input);
$action = (string)($_GET['action'] ?? $params['action'] ?? 'listar');

if ($metodo === 'GET') {
    if ($action === 'listar') {
        $res = $controller->listar([
            'q' => $_GET['q'] ?? '',
            'estado' => $_GET['estado'] ?? '',
            'desde' => $_GET['desde'] ?? '',
            'hasta' => $_GET['hasta'] ?? '',
            'orden' => $_GET['orden'] ?? 'recientes',
        ]);

        jsonResponse(
            $res['success'],
            $res['message'],
            $res['success'] ? [
                'pedidos' => $res['pedidos'],
                'resumen' => $res['resumen'],
                'estados' => $res['estados'],
            ] : null,
            $res['success'] ? 200 : 400
        );
    }

    if ($action === 'detalle') {
        $idPedido = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$idPedido || $idPedido <= 0) {
            jsonResponse(false, 'El ID del pedido no es válido.', null, 400);
        }

        $res = $controller->detalle((int)$idPedido);
        jsonResponse(
            $res['success'],
            $res['message'],
            $res['success'] ? $res['pedido'] : null,
            $res['success'] ? 200 : 404
        );
    }

    jsonResponse(false, 'Acción no reconocida.', null, 400);
}

if ($metodo !== 'POST') {
    jsonResponse(false, 'Método HTTP no permitido.', null, 405);
}

$tokenSesion = (string)($_SESSION['csrf_admin_pedidos'] ?? '');
$tokenRecibido = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? $params['csrf_token'] ?? '');

if ($tokenSesion === '' || $tokenRecibido === '' || !hash_equals($tokenSesion, $tokenRecibido)) {
    jsonResponse(false, 'Token de seguridad inválido o expirado.', null, 419);
}

if ($action === 'cambiar_estado') {
    $idPedido = filter_var($params['id_pedido'] ?? null, FILTER_VALIDATE_INT);
    $nuevoEstado = filter_var($params['id_estado_pedido'] ?? null, FILTER_VALIDATE_INT);

    if (!$idPedido || $idPedido <= 0 || !$nuevoEstado || $nuevoEstado <= 0) {
        jsonResponse(false, 'Los datos del pedido no son válidos.', null, 400);
    }

    $res = $controller->cambiarEstado((int)$idPedido, (int)$nuevoEstado);
    jsonResponse(
        $res['success'],
        $res['message'],
        $res['success'] ? $res['pedido'] : null,
        $res['success'] ? 200 : 400
    );
}

if ($action === 'eliminar') {
    $idPedido = filter_var($params['id_pedido'] ?? null, FILTER_VALIDATE_INT);

    if (!$idPedido || $idPedido <= 0) {
        jsonResponse(false, 'El ID del pedido no es válido.', null, 400);
    }

    $res = $controller->eliminarCancelado((int)$idPedido);
    jsonResponse(
        $res['success'],
        $res['message'],
        null,
        $res['success'] ? 200 : 400
    );
}

jsonResponse(false, 'Acción no reconocida.', null, 400);
