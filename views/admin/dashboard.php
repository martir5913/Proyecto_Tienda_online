<?php
// Vista: Dashboard de Administración
$tituloPagina = "Panel Administrador | Doméstik";
$scriptEspecifico = "admin.js";

require_once dirname(__DIR__, 2) . '/app/middlewares/AuthMiddleware.php';
require_once dirname(__DIR__, 2) . '/app/controllers/AdminController.php';

use App\Middlewares\AuthMiddleware;
use App\Controllers\AdminController;

AuthMiddleware::verificarAdmin();

$adminCtrl = new AdminController();
$metricas = $adminCtrl->getMetricasDashboard();

require_once dirname(__DIR__) . '/layouts/header.php';
?>

<div class="container-fluid py-4 px-lg-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-speedometer2 me-2 text-primary"></i>Panel de Control Administrativo</h3>
            <p class="text-muted small mb-0">Gestión centralizada de catálogo, inventario, pedidos y usuarios</p>
        </div>
        <div>
            <span class="badge bg-primary px-3 py-2">
                <i class="bi bi-person-badge me-1"></i> Admin: <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>
            </span>
        </div>
    </div>

    <!-- Tarjetas de Métricas Rápidas -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-primary-subtle text-primary rounded-3 me-3">
                        <i class="bi bi-currency-dollar fs-3"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold">Ventas Totales</span>
                        <h4 class="fw-bold mb-0">Q <?= number_format($metricas['ventas_totales'], 2) ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-warning-subtle text-warning rounded-3 me-3">
                        <i class="bi bi-clock-history fs-3"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold">Pedidos Pendientes</span>
                        <h4 class="fw-bold mb-0"><?= $metricas['pedidos_pendientes'] ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-success-subtle text-success rounded-3 me-3">
                        <i class="bi bi-box-seam fs-3"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold">Productos en Stock</span>
                        <h4 class="fw-bold mb-0"><?= $metricas['total_productos'] ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-info-subtle text-info rounded-3 me-3">
                        <i class="bi bi-people fs-3"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold">Clientes Registrados</span>
                        <h4 class="fw-bold mb-0"><?= $metricas['total_clientes'] ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secciones del Panel -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-gear me-2"></i>Módulos de Gestión</h5>
                <p class="text-muted small">Acceso directo a las operaciones CRUD normalizadas del sistema:</p>
                <div class="list-group list-group-flush">
                    <a href="<?= BASE_URL ?>/index.php?ruta=admin_productos" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div><i class="bi bi-boxes me-2 text-primary"></i> Administrar inventario y productos</div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                    <a href="<?= BASE_URL ?>/index.php?ruta=admin_categorias" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div><i class="bi bi-tags me-2 text-primary"></i> Administrar categorías</div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                    <a href="<?= BASE_URL ?>/index.php?ruta=admin_pedidos"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-receipt me-2 text-primary"></i>
                            Control de Pedidos y Envíos
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                    <a href="<?= BASE_URL ?>/index.php?ruta=admin_usuarios"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-people-fill me-2 text-primary"></i>
                            Gestión de Usuarios y Roles
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                    <a href="<?= BASE_URL ?>/index.php?ruta=admin_apis"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-braces-asterisk me-2 text-primary"></i>
                            Pruebas de API
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                 </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-database-check me-2"></i>Estado del Servidor y Base de Datos</h5>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i><strong>Motor BD:</strong> MySQL 8.0 (InnoDB) Docker</li>
                    <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i><strong>Transacciones:</strong> ACID habilitado y verificado</li>
                    <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i><strong>Normalización:</strong> 3FN sin tipos ENUM</li>
                    <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i><strong>API Diagnóstico:</strong> <a href="<?= BASE_URL ?>/api/test_db.php" target="_blank" class="fw-bold">/api/test_db.php</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>
