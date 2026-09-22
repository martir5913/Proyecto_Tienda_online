<?php

declare(strict_types=1);

$tituloPagina = 'Control de Pedidos y Envíos | Doméstik';
$scriptEspecifico = 'admin_pedidos.js';

require_once dirname(__DIR__, 2) . '/app/middlewares/AuthMiddleware.php';

use App\Middlewares\AuthMiddleware;

AuthMiddleware::verificarAdmin();

if (empty($_SESSION['csrf_admin_pedidos'])) {
    $_SESSION['csrf_admin_pedidos'] = bin2hex(random_bytes(32));
}

$csrfToken = (string)$_SESSION['csrf_admin_pedidos'];

require_once dirname(__DIR__) . '/layouts/header.php';
?>

<div
    class="container-fluid py-4 px-lg-5"
    id="admin-pedidos-modulo"
    data-api-url="<?= BASE_URL ?>/api/admin_pedidos.php"
    data-csrf-token="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"
>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-receipt-cutoff me-2 text-primary"></i>Control de Pedidos y Envíos
            </h3>
            <p class="text-muted small mb-0">
                Gestión administrativa del ciclo de los pedidos registrados.
            </p>
        </div>
        <a href="<?= BASE_URL ?>/index.php?ruta=admin_dashboard" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Volver al panel
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
                <span class="text-muted small">Pendientes</span>
                <h4 class="fw-bold mb-0" id="resumen-pendientes">0</h4>
            </div>
        </div>
        <div class="col-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
                <span class="text-muted small">Procesando</span>
                <h4 class="fw-bold mb-0" id="resumen-procesando">0</h4>
            </div>
        </div>
        <div class="col-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
                <span class="text-muted small">Enviados</span>
                <h4 class="fw-bold mb-0" id="resumen-enviados">0</h4>
            </div>
        </div>
        <div class="col-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
                <span class="text-muted small">Entregados</span>
                <h4 class="fw-bold mb-0" id="resumen-entregados">0</h4>
            </div>
        </div>
        <div class="col-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
                <span class="text-muted small">Cancelados</span>
                <h4 class="fw-bold mb-0" id="resumen-cancelados">0</h4>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <form id="form-filtros-pedidos" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="pedido-busqueda" class="form-label small fw-semibold">Buscar pedido o cliente</label>
                    <input
                        type="search"
                        class="form-control"
                        id="pedido-busqueda"
                        name="q"
                        maxlength="100"
                        autocomplete="off"
                        placeholder="No. pedido, nombre o correo"
                    >
                </div>

                <div class="col-md-2">
                    <label for="pedido-estado" class="form-label small fw-semibold">Estado</label>
                    <select class="form-select" id="pedido-estado" name="estado">
                        <option value="">Todos</option>
                        <option value="1">Pendiente</option>
                        <option value="2">Procesando</option>
                        <option value="3">Enviado</option>
                        <option value="4">Entregado</option>
                        <option value="5">Cancelado</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="pedido-desde" class="form-label small fw-semibold">Desde</label>
                    <input type="date" class="form-control" id="pedido-desde" name="desde">
                </div>

                <div class="col-md-2">
                    <label for="pedido-hasta" class="form-label small fw-semibold">Hasta</label>
                    <input type="date" class="form-control" id="pedido-hasta" name="hasta">
                </div>

                <div class="col-md-2">
                    <label for="pedido-orden" class="form-label small fw-semibold">Orden</label>
                    <select class="form-select" id="pedido-orden" name="orden">
                        <option value="recientes">Más recientes</option>
                        <option value="antiguos">Más antiguos</option>
                        <option value="total_desc">Mayor total</option>
                        <option value="total_asc">Menor total</option>
                    </select>
                </div>

                <div class="col-12 d-flex justify-content-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-limpiar-pedidos">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Limpiar filtros
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="alerta-admin-pedidos" class="d-none" role="alert"></div>

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">Pedidos registrados</h5>
            <span class="text-muted small" id="contador-admin-pedidos">Cargando...</span>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Pedido</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Artículos</th>
                        <th>Total</th>
                        <th>Pago</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-admin-pedidos">
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>
                            Cargando pedidos...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal detalle -->
<div class="modal fade" id="modalDetallePedidoAdmin" tabindex="-1" aria-labelledby="modalDetallePedidoAdminTitulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalDetallePedidoAdminTitulo">Detalle del pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalDetallePedidoAdminContenido"></div>
        </div>
    </div>
</div>

<!-- Modal confirmación de cambio de estado -->
<div class="modal fade" id="modalConfirmarEstadoPedido" tabindex="-1" aria-labelledby="modalConfirmarEstadoPedidoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalConfirmarEstadoPedidoTitulo">Confirmar acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="texto-confirmar-estado-pedido"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary-app" id="btn-confirmar-estado-pedido">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>
