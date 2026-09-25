<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/models/Model.php';
require_once dirname(__DIR__) . '/app/models/Marca.php';

use App\Models\Marca;

header('Content-Type: application/json; charset=utf-8');

$marcaModel = new Marca();
$marcas = $marcaModel->obtenerTodas();

jsonResponse(true, "Marcas obtenidas exitosamente.", [
    'total'  => count($marcas),
    'marcas' => $marcas
]);
