<?php
 // * API Endpoint: Pedidos y Checkout Transaccional
 

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/models/Model.php';
require_once dirname(__DIR__) . '/app/models/Producto.php';
require_once dirname(__DIR__) . '/app/models/Pedido.php';
require_once dirname(__DIR__) . '/app/controllers/CarritoController.php';
require_once dirname(__DIR__) . '/app/controllers/PedidoController.php';
require_once dirname(__DIR__) . '/app/middlewares/AuthMiddleware.php';

use App\Controllers\PedidoController;
use App\Middlewares\AuthMiddleware;

header('Content-Type: application/json; charset=utf-8');

$pedidoCtrl = new PedidoController();
$action = $_GET['action'] ?? ($_POST['action'] ?? 'checkout');

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$params = array_merge($_POST, $input);

switch ($action) {
    case 'checkout':
        AuthMiddleware::verificarAutenticado();
        $idUsuario = (int)$_SESSION['usuario']['id_usuario'];
        $idMetodoPago = (int)($params['id_metodo_pago'] ?? 1);
        $direccion = trim($params['direccion_envio'] ?? ($_SESSION['usuario']['direccion'] ?? ''));
        $notas = trim($params['notas'] ?? '');

        if (empty($direccion)) {
            jsonResponse(false, "La dirección de entrega es requerida.", null, 400);
        }

        $res = $pedidoCtrl->procesarCheckout($idUsuario, $idMetodoPago, $direccion, $notas);
        jsonResponse($res['success'], $res['message'], $res, $res['success'] ? 201 : 400);
        break;

    case 'historial':
        AuthMiddleware::verificarAutenticado();
        $idUsuario = (int)$_SESSION['usuario']['id_usuario'];
        $historial = $pedidoCtrl->getHistorial($idUsuario);
        jsonResponse(true, "Historial de pedidos recuperado.", ['total' => count($historial), 'pedidos' => $historial]);
        break;

    default:
        jsonResponse(false, "Acción no reconocida.", null, 400);
}
