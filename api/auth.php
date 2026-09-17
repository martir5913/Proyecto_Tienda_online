<?php
/**
 * API Endpoint: Autenticación de Usuarios
 * Acciones soportadas: login, registro, logout, check
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/models/Model.php';
require_once dirname(__DIR__) . '/app/models/Usuario.php';
require_once dirname(__DIR__) . '/app/controllers/AuthController.php';

use App\Controllers\AuthController;

header('Content-Type: application/json; charset=utf-8');

$authCtrl = new AuthController();
$action = $_GET['action'] ?? ($_POST['action'] ?? 'check');

// Capturar datos JSON si vienen por body
$inputJSON = json_decode(file_get_contents('php://input'), true) ?? [];
$params = array_merge($_POST, $inputJSON);

switch ($action) {
    case 'login':
        $correo = trim($params['correo'] ?? '');
        $password = trim($params['password'] ?? '');
        if (empty($correo) || empty($password)) {
            jsonResponse(false, "El correo y la contraseña son obligatorios.", null, 400);
        }
        $res = $authCtrl->login($correo, $password);
        jsonResponse($res['success'], $res['message'], $res['usuario'] ?? null, $res['success'] ? 200 : 401);
        break;

    case 'registro':
        if (empty($params['nombre']) || empty($params['correo']) || empty($params['password'])) {
            jsonResponse(false, "Nombre, correo y contraseña son campos obligatorios.", null, 400);
        }
        $res = $authCtrl->registrar($params);
        jsonResponse($res['success'], $res['message'], ['id_usuario' => $res['id_usuario'] ?? null], $res['success'] ? 201 : 400);
        break;

    case 'logout':
        $authCtrl->logout();
        jsonResponse(true, "Sesión cerrada exitosamente.");
        break;

    case 'check':
        if (estaAutenticado()) {
            jsonResponse(true, "Usuario autenticado.", $_SESSION['usuario']);
        } else {
            jsonResponse(false, "No hay sesión activa.", null, 401);
        }
        break;

    default:
        jsonResponse(false, "Acción de autenticación no reconocida.", null, 400);
}
