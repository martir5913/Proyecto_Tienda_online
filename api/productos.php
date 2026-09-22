<?php
 // * API Endpoint: Productos (Catálogo, Filtros y Detalle)
 

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/models/Model.php';
require_once dirname(__DIR__) . '/app/models/Producto.php';
require_once dirname(__DIR__) . '/app/models/Categoria.php';
require_once dirname(__DIR__) . '/app/models/Marca.php';
require_once dirname(__DIR__) . '/app/controllers/ProductoController.php';

use App\Controllers\ProductoController;

header('Content-Type: application/json; charset=utf-8');

$productoCtrl = new ProductoController();
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($id) {
    $producto = $productoCtrl->getDetalle($id);
    if ($producto) {
        jsonResponse(true, "Detalle del producto obtenido.", $producto);
    } else {
        jsonResponse(false, "Producto no encontrado.", null, 404);
    }
} else {
    $filtros = [
        'categoria'  => $_GET['categoria'] ?? null,
        'marca'      => $_GET['marca'] ?? null,
        'busqueda'   => $_GET['q'] ?? ($_GET['busqueda'] ?? null),
        'precio_min' => $_GET['min'] ?? ($_GET['precio_min'] ?? null),
        'precio_max' => $_GET['max'] ?? ($_GET['precio_max'] ?? null)
    ];
    $productos = $productoCtrl->getCatalogo($filtros);
    jsonResponse(true, "Catálogo de productos obtenido exitosamente.", [
        'total'     => count($productos),
        'productos' => $productos
    ]);
}
