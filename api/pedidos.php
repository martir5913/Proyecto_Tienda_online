<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/models/Model.php';
require_once dirname(__DIR__) . '/app/models/Producto.php';
require_once dirname(__DIR__) . '/app/controllers/CarritoController.php';
require_once dirname(__DIR__) . '/app/controllers/PedidoController.php';
require_once dirname(__DIR__) . '/app/middlewares/AuthMiddleware.php';

use App\Controllers\PedidoController;
use App\Middlewares\AuthMiddleware;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$pedidoCtrl = new PedidoController();
$action = (string)($_GET['action'] ?? $_POST['action'] ?? 'checkout');

$accionesPermitidas = ['checkout', 'historial', 'detalle'];

if (!in_array($action, $accionesPermitidas, true)) {
    jsonResponse(false, 'Acción no reconocida.', null, 400);
}

if ($action === 'checkout') {
    AuthMiddleware::verificarAutenticado();

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
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

    $csrfSesion = isset($_SESSION['csrf_checkout']) && is_string($_SESSION['csrf_checkout'])
        ? $_SESSION['csrf_checkout']
        : '';

    if (
        $csrfRecibido === '' ||
        $csrfSesion === '' ||
        !hash_equals($csrfSesion, $csrfRecibido)
    ) {
        jsonResponse(false, 'La solicitud no pudo ser validada. Recarga la página e inténtalo nuevamente.', null, 403);
    }

    $idUsuario = filter_var(
        $_SESSION['usuario']['id_usuario'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    $idMetodoPago = filter_var(
        $params['id_metodo_pago'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($idUsuario === false || $idMetodoPago === false) {
        jsonResponse(false, 'Los datos del pedido no son válidos.', null, 422);
    }

    $direccion = isset($params['direccion_envio']) && is_string($params['direccion_envio'])
        ? $params['direccion_envio']
        : '';

    // Enriquecer notas con información de facturación y comprobante de pago
    $notasArray = [];
    if (!empty($params['notas'])) {
        $notasArray[] = trim((string)$params['notas']);
    }

    $nit = !empty($params['nit']) ? trim((string)$params['nit']) : 'C/F';
    $facturaNombre = !empty($params['facturar_a']) ? trim((string)$params['facturar_a']) : ($_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellido']);
    $notasArray[] = "NIT: {$nit} | Facturar a: {$facturaNombre}";

    if (!empty($params['numero_boleta'])) {
        $notasArray[] = "Boleta/Ref Transferencia: " . trim((string)$params['numero_boleta']);
    }

    if (!empty($params['tarjeta_terminacion'])) {
        $notasArray[] = "Tarjeta autorizada terminación: **** " . trim((string)$params['tarjeta_terminacion']);
    }

    $notas = implode(" - ", $notasArray);

    $resultado = $pedidoCtrl->procesarCheckout(
        (int)$idUsuario,
        (int)$idMetodoPago,
        $direccion,
        $notas
    );

    if ($resultado['success']) {
        $_SESSION['csrf_checkout'] = bin2hex(random_bytes(32));

        jsonResponse(
            true,
            $resultado['message'],
            $resultado,
            201
        );
    }

    jsonResponse(
        false,
        $resultado['message'],
        null,
        422
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

if ($action === 'detalle') {
    $idPedido = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!$idPedido || $idPedido <= 0) {
        jsonResponse(false, 'ID de pedido no proporcionado o inválido.', null, 400);
    }

    $esAdmin = esAdmin();
    $detalle = $pedidoCtrl->getDetalle((int)$idPedido, (int)$idUsuario, $esAdmin);

    if (!$detalle) {
        jsonResponse(false, 'Pedido no encontrado o acceso no autorizado.', null, 404);
    }

    jsonResponse(true, 'Detalle de pedido obtenido exitosamente.', $detalle);
}

if ($action === 'historial') {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
        header('Allow: GET');
        jsonResponse(false, 'Método HTTP no permitido.', null, 405);
    }

    $historial = $pedidoCtrl->getHistorial((int)$idUsuario);

    jsonResponse(
        true,
        'Historial de pedidos recuperado.',
        [
            'total' => count($historial),
            'pedidos' => $historial,
        ]
    );
}
