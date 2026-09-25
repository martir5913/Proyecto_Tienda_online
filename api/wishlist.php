<?php
 // * API Endpoint: Wishlist (Lista de Deseos)
 

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/models/Model.php';
require_once dirname(__DIR__) . '/app/models/Wishlist.php';
require_once dirname(__DIR__) . '/app/controllers/WishlistController.php';
require_once dirname(__DIR__) . '/app/middlewares/AuthMiddleware.php';

use App\Controllers\WishlistController;
use App\Middlewares\AuthMiddleware;

header('Content-Type: application/json; charset=utf-8');

AuthMiddleware::verificarAutenticado();
$idUsuario = (int)$_SESSION['usuario']['id_usuario'];

$wishlistCtrl = new WishlistController();
$action = $_GET['action'] ?? ($_POST['action'] ?? 'get');
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$params = array_merge($_POST, $input);

switch ($action) {
    case 'add':
        $idProducto = (int)($params['id_producto'] ?? 0);
        $res = $wishlistCtrl->agregar($idUsuario, $idProducto);
        jsonResponse($res['success'], $res['message'], null, $res['success'] ? 200 : 400);
        break;

    case 'remove':
        $idProducto = (int)($params['id_producto'] ?? 0);
        $res = $wishlistCtrl->eliminar($idUsuario, $idProducto);
        jsonResponse($res['success'], $res['message'], null, $res['success'] ? 200 : 400);
        break;

    case 'get':
    default:
        $favoritos = $wishlistCtrl->getFavoritos($idUsuario);
        jsonResponse(true, "Favoritos obtenidos.", ['total' => count($favoritos), 'wishlist' => $favoritos]);
        break;
}
