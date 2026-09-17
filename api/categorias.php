<?php
/**
 * API Endpoint: Categorías
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/models/Model.php';
require_once dirname(__DIR__) . '/app/models/Categoria.php';
require_once dirname(__DIR__) . '/app/controllers/CategoriaController.php';

use App\Controllers\CategoriaController;

header('Content-Type: application/json; charset=utf-8');

$categoriaCtrl = new CategoriaController();
$categorias = $categoriaCtrl->getTodas();

jsonResponse(true, "Categorías obtenidas exitosamente.", [
    'total'      => count($categorias),
    'categorias' => $categorias
]);
