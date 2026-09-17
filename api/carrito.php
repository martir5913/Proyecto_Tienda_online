<?php
/**
 * API Endpoint: Carrito de Compras
 * Acciones: add, update, remove, get, clear
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/models/Model.php';
require_once dirname(__DIR__) . '/app/models/Producto.php';
require_once dirname(__DIR__) . '/app/controllers/CarritoController.php';

use App\Controllers\CarritoController;

header('Content-Type: application/json; charset=utf-8');

$carritoCtrl = new CarritoController();
$action = $_GET['action'] ?? ($_POST['action'] ?? 'get');
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$params = array_merge($_POST, $input);

switch ($action) {
    case 'add':
        $id = (int)($params['id_producto'] ?? 0);
        $cant = max(1, (int)($params['cantidad'] ?? 1));
        $res = $carritoCtrl->agregar($id, $cant);
        jsonResponse($res['success'], $res['message'], $res, $res['success'] ? 200 : 400);
        break;

    case 'update':
        $id = (int)($params['id_producto'] ?? 0);
        $cant = (int)($params['cantidad'] ?? 1);
        $res = $carritoCtrl->actualizarCantidad($id, $cant);
        jsonResponse($res['success'], $res['message'], $res['resumen'] ?? null, $res['success'] ? 200 : 400);
        break;

    case 'remove':
        $id = (int)($params['id_producto'] ?? 0);
        $res = $carritoCtrl->eliminar($id);
        jsonResponse($res['success'], $res['message'], $res, 200);
        break;

    case 'clear':
        $carritoCtrl->vaciar();
        jsonResponse(true, "Carrito vaciado.", $carritoCtrl->obtenerResumen());
        break;

    case 'get':
    default:
        jsonResponse(true, "Resumen del carrito.", $carritoCtrl->obtenerResumen());
        break;
}
