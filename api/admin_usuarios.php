<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/models/Model.php';
require_once dirname(__DIR__) . '/app/models/Usuario.php';
require_once dirname(__DIR__) . '/app/controllers/UsuarioAdminController.php';
require_once dirname(__DIR__) . '/app/middlewares/AuthMiddleware.php';

use App\Controllers\UsuarioAdminController;
use App\Middlewares\AuthMiddleware;

header('Content-Type: application/json; charset=utf-8');

// Seguridad: Verificar que el usuario tenga rol de Administrador
AuthMiddleware::verificarAdmin();

$controller = new UsuarioAdminController();
$metodo = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$input = json_decode(file_get_contents('php://input'), true);
$input = is_array($input) ? $input : [];
$params = array_merge($_POST, $input);
$action = (string)($_GET['action'] ?? $params['action'] ?? 'listar');

if ($metodo === 'GET') {
    if ($action === 'listar') {
        $res = $controller->listar([
            'q'         => $_GET['q'] ?? '',
            'id_rol'    => $_GET['id_rol'] ?? '',
            'id_estado' => $_GET['id_estado'] ?? '',
            'orden'     => $_GET['orden'] ?? 'recientes',
        ]);

        jsonResponse(
            $res['success'],
            $res['message'],
            $res['success'] ? [
                'usuarios' => $res['usuarios'],
                'resumen'  => $res['resumen'],
                'roles'    => $res['roles'],
                'estados'  => $res['estados'],
            ] : null,
            $res['success'] ? 200 : 400
        );
    }

    if ($action === 'detalle') {
        $idUsuario = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$idUsuario || $idUsuario <= 0) {
            jsonResponse(false, 'El ID de usuario no es válido.', null, 400);
        }

        $res = $controller->detalle((int)$idUsuario);
        jsonResponse(
            $res['success'],
            $res['message'],
            $res['success'] ? $res['usuario'] : null,
            $res['success'] ? 200 : 404
        );
    }
}

if ($metodo === 'POST') {
    $csrfToken = (string)($params['csrf_token'] ?? '');

    if ($action === 'actualizar') {
        $idUsuario = (int)($params['id_usuario'] ?? 0);
        if ($idUsuario <= 0) {
            jsonResponse(false, 'ID de usuario inválido.', null, 400);
        }

        $res = $controller->actualizar($idUsuario, $params, $csrfToken);
        jsonResponse($res['success'], $res['message'], $res['usuario'] ?? null, $res['success'] ? 200 : 400);
    }

    if ($action === 'cambiar_estado') {
        $idUsuario = (int)($params['id_usuario'] ?? 0);
        $idEstado = (int)($params['id_estado_usuario'] ?? 0);

        if ($idUsuario <= 0 || $idEstado <= 0) {
            jsonResponse(false, 'Datos incompletos para actualizar el estado.', null, 400);
        }

        $res = $controller->cambiarEstado($idUsuario, $idEstado, $csrfToken);
        jsonResponse($res['success'], $res['message'], $res['resumen'] ?? null, $res['success'] ? 200 : 400);
    }

    if ($action === 'cambiar_rol') {
        $idUsuario = (int)($params['id_usuario'] ?? 0);
        $idRol = (int)($params['id_rol'] ?? 0);

        if ($idUsuario <= 0 || $idRol <= 0) {
            jsonResponse(false, 'Datos incompletos para actualizar el rol.', null, 400);
        }

        $res = $controller->cambiarRol($idUsuario, $idRol, $csrfToken);
        jsonResponse($res['success'], $res['message'], $res['resumen'] ?? null, $res['success'] ? 200 : 400);
    }
}

jsonResponse(false, 'Acción o método no admitido para administración de usuarios.', null, 405);
