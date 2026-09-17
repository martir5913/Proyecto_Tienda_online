<?php
// Header Global - Layout Principal
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/app/controllers/CarritoController.php';

use App\Controllers\CarritoController;

$carritoCtrl = new CarritoController();
$totalCarrito = $carritoCtrl->contarItems();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloPagina ?? 'ElectroHogar | Tienda en Línea de Electrodomésticos' ?></title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/public/css/app.css" rel="stylesheet">
</head>
<body>

    <!-- Barra de Navegación Principal -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>/index.php">
                <i class="bi bi-lightning-charge-fill text-warning me-2 fs-4"></i>
                <span>ElectroHogar</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <!-- Buscador rápido en Header -->
                <form class="d-flex mx-auto my-2 my-lg-0 w-50" action="<?= BASE_URL ?>/index.php" method="GET">
                    <input type="hidden" name="ruta" value="catalogo">
                    <div class="input-group">
                        <input class="form-control" type="search" name="q" placeholder="Buscar refrigeradoras, estufas, lavadoras..." aria-label="Buscar">
                        <button class="btn btn-primary-app" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>

                <!-- Accesos y Sesión -->
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/index.php?ruta=catalogo">
                            <i class="bi bi-grid me-1"></i> Catálogo
                        </a>
                    </li>

                    <?php if (estaAutenticado()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/index.php?ruta=wishlist">
                                <i class="bi bi-heart me-1"></i> Deseos
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link position-relative" href="<?= BASE_URL ?>/index.php?ruta=carrito">
                            <i class="bi bi-cart3 fs-5"></i>
                            <span class="badge rounded-pill bg-danger cart-count-badge position-absolute top-0 start-100 translate-middle" 
                                  style="<?= $totalCarrito > 0 ? '' : 'display:none;' ?>">
                                <?= $totalCarrito ?>
                            </span>
                        </a>
                    </li>

                    <?php if (estaAutenticado()): ?>
                        <li class="nav-item dropdown ms-2">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle fs-5 me-1"></i>
                                <span><?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/index.php?ruta=mis_pedidos"><i class="bi bi-bag-check me-2"></i>Mis Pedidos</a></li>
                                <?php if (esAdmin()): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-primary" href="<?= BASE_URL ?>/index.php?ruta=admin_dashboard"><i class="bi bi-speedometer2 me-2"></i>Panel Administrador</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/index.php?ruta=logout"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-2">
                            <a class="btn btn-sm btn-outline-light" href="<?= BASE_URL ?>/index.php?ruta=login">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sub-barra de categorías -->
    <div class="category-bar d-none d-md-block">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <a href="<?= BASE_URL ?>/index.php?ruta=catalogo&categoria=1" class="cat-link"><i class="bi bi-snow me-1"></i> Refrigeración</a>
                <a href="<?= BASE_URL ?>/index.php?ruta=catalogo&categoria=2" class="cat-link"><i class="bi bi-water me-1"></i> Lavado y Secado</a>
                <a href="<?= BASE_URL ?>/index.php?ruta=catalogo&categoria=3" class="cat-link"><i class="bi bi-fire me-1"></i> Cocción y Estufas</a>
                <a href="<?= BASE_URL ?>/index.php?ruta=catalogo&categoria=4" class="cat-link"><i class="bi bi-cup-hot me-1"></i> Pequeños Electrodomésticos</a>
                <a href="<?= BASE_URL ?>/index.php?ruta=catalogo&categoria=5" class="cat-link"><i class="bi bi-wind me-1"></i> Climatización</a>
            </div>
            <div>
                <span class="badge bg-warning text-dark"><i class="bi bi-truck me-1"></i> Envíos a todo el país</span>
            </div>
        </div>
    </div>

    <!-- Contenedor Principal -->
    <main class="flex-grow-1">
