<?php
// Vista: Historial de Compras del Cliente (Mis Pedidos)
$tituloPagina = "Mis Pedidos | Doméstik";

require_once dirname(__DIR__) . '/app/middlewares/AuthMiddleware.php';
require_once dirname(__DIR__) . '/app/controllers/PedidoController.php';

use App\Middlewares\AuthMiddleware;
use App\Controllers\PedidoController;

AuthMiddleware::verificarAutenticado();
$idUsuario = (int)$_SESSION['usuario']['id_usuario'];

$pedidoCtrl = new PedidoController();
$pedidos = $pedidoCtrl->getHistorial($idUsuario);

require_once __DIR__ . '/layouts/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-bag-check me-2"></i>Mis Pedidos</h3>
            <p class="text-muted small mb-0">Consulta el estado y detalle de tus compras de electrodomésticos</p>
        </div>
        <a href="<?= BASE_URL ?>/index.php?ruta=catalogo" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-cart-plus me-1"></i> Nueva Compra
        </a>
    </div>

    <?php if (isset($_GET['exito']) && isset($_GET['orden'])): ?>
        <div class="alert alert-success d-flex align-items-center mb-4">
            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
            <div>
                <h6 class="mb-0 fw-bold">¡Pedido realizado con éxito!</h6>
                <small>Tu orden transaccional <strong><?= htmlspecialchars($_GET['orden']) ?></strong> ha sido guardada en la base de datos con motor ACID.</small>
            </div>
        </div>
    <?php endif; ?>

    <?php if (empty($pedidos)): ?>
        <div class="text-center py-5 bg-white rounded-3 shadow-sm">
            <i class="bi bi-box-seam fs-1 text-muted"></i>
            <h5 class="mt-3 text-muted">Aún no has realizado pedidos</h5>
            <p class="text-muted small">Tus compras aparecerán listadas aquí con su respectivo estado.</p>
            <a href="<?= BASE_URL ?>/index.php?ruta=catalogo" class="btn btn-primary-app">Ver Catálogo</a>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No. Pedido</th>
                            <th>Fecha</th>
                            <th>Artículos</th>
                            <th>Método de Pago</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $p): ?>
                            <tr>
                                <td class="fw-bold text-primary">
                                    <a href="#" onclick="verDetallePedido(<?= $p['id_pedido'] ?>); return false;" class="text-decoration-none">
                                        <?= htmlspecialchars($p['numero_pedido']) ?>
                                    </a>
                                </td>
                                <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($p['fecha_pedido'])) ?></td>
                                <td><?= $p['total_articulos'] ?> electrodomésticos</td>
                                <td class="small"><?= htmlspecialchars($p['nombre_metodo']) ?></td>
                                <td class="fw-bold">Q <?= number_format((float)$p['total'], 2) ?></td>
                                <td>
                                    <?php
                                    $claseBadge = match ($p['nombre_estado']) {
                                        'Pendiente' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                        'Procesando' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                        'Enviado' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                                        'Entregado' => 'bg-success-subtle text-success border border-success-subtle',
                                        'Cancelado' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                        default => 'bg-secondary-subtle text-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $claseBadge ?> px-2 py-1">
                                        <?= htmlspecialchars($p['nombre_estado']) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="verDetallePedido(<?= $p['id_pedido'] ?>)" title="Ver Detalle de la Orden">
                                            <i class="bi bi-eye"></i> Detalle
                                        </button>
                                        <a href="<?= BASE_URL ?>/index.php?ruta=factura&id=<?= $p['id_pedido'] ?>" target="_blank" class="btn btn-outline-secondary" title="Ver / Imprimir Factura Electrónica">
                                            <i class="bi bi-printer"></i> Factura
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Detalle del Pedido -->
<div class="modal fade" id="modal-detalle-pedido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="modal-pedido-titulo">
                    <i class="bi bi-receipt me-2 text-primary"></i>Detalle de Pedido
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4" id="modal-pedido-contenido">
                <div class="text-center py-4 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                    Cargando información del pedido...
                </div>
            </div>
            <div class="modal-footer border-0 pt-0" id="modal-pedido-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                <a href="#" id="btn-modal-factura" target="_blank" class="btn btn-primary-app btn-sm">
                    <i class="bi bi-printer me-1"></i> Imprimir Factura Digital
                </a>
            </div>
        </div>
    </div>
</div>

<script>
let modalDetalleInstance = null;

async function verDetallePedido(idPedido) {
    const modalEl = document.getElementById('modal-detalle-pedido');
    const contenidoEl = document.getElementById('modal-pedido-contenido');
    const btnFactura = document.getElementById('btn-modal-factura');

    if (!modalDetalleInstance) {
        modalDetalleInstance = new bootstrap.Modal(modalEl);
    }

    btnFactura.href = `<?= BASE_URL ?>/index.php?ruta=factura&id=${idPedido}`;
    contenidoEl.innerHTML = `
        <div class="text-center py-4 text-muted">
            <div class="spinner-border spinner-border-sm text-primary me-2"></div>
            Cargando información del pedido...
        </div>
    `;

    modalDetalleInstance.show();

    try {
        const resp = await fetch(`api/pedidos.php?action=detalle&id=${idPedido}`);
        const res = await resp.json();

        if (res.success && res.data) {
            const p = res.data;
            const itemsHtml = (p.items || []).map(it => `
                <tr>
                    <td>
                        <strong class="text-dark d-block">${it.nombre_producto}</strong>
                        <small class="text-muted">${it.nombre_marca} · Mod: ${it.codigo_modelo}</small>
                    </td>
                    <td class="text-center">${it.cantidad}</td>
                    <td class="text-end">Q ${parseFloat(it.precio_unitario).toFixed(2)}</td>
                    <td class="text-end fw-bold text-dark">Q ${parseFloat(it.subtotal).toFixed(2)}</td>
                </tr>
            `).join('');

            contenidoEl.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <span class="text-muted small">No. de Pedido:</span>
                        <strong class="text-primary fs-6 ms-1">${p.numero_pedido}</strong>
                    </div>
                    <div>
                        <span class="badge bg-primary px-3 py-1">${p.nombre_estado}</span>
                    </div>
                </div>

                <div class="row g-3 mb-3 small">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <strong class="text-dark d-block mb-1"><i class="bi bi-geo-alt me-1 text-primary"></i>Dirección de Entrega:</strong>
                            <span class="text-muted">${p.direccion_envio}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <strong class="text-dark d-block mb-1"><i class="bi bi-credit-card me-1 text-primary"></i>Método de Pago:</strong>
                            <span class="text-muted">${p.nombre_metodo}</span>
                            ${p.notas ? `<small class="text-muted d-block mt-1">${p.notas}</small>` : ''}
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold mb-2 small text-uppercase text-muted">Productos</h6>
                <div class="table-responsive border rounded-3 mb-3">
                    <table class="table table-sm align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-center" style="width: 70px;">Cant.</th>
                                <th class="text-end" style="width: 110px;">Unitario</th>
                                <th class="text-end" style="width: 110px;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>${itemsHtml}</tbody>
                    </table>
                </div>

                <div class="row justify-content-end">
                    <div class="col-md-5 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Subtotal:</span>
                            <span class="fw-semibold">Q ${parseFloat(p.subtotal).toFixed(2)}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">IVA (12%):</span>
                            <span class="fw-semibold">Q ${parseFloat(p.impuesto).toFixed(2)}</span>
                        </div>
                        <div class="d-flex justify-content-between fs-6 fw-bold border-top pt-2">
                            <span>Total:</span>
                            <span class="text-primary">Q ${parseFloat(p.total).toFixed(2)}</span>
                        </div>
                    </div>
                </div>
            `;
        } else {
            contenidoEl.innerHTML = `<div class="alert alert-danger mb-0">${res.message || 'No se pudo cargar el detalle del pedido.'}</div>`;
        }
    } catch (e) {
        contenidoEl.innerHTML = '<div class="alert alert-danger mb-0">Error de conexión al obtener el detalle del pedido.</div>';
    }
}
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>

