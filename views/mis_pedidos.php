<?php
/**
 * Vista: Historial de Compras del Cliente (Mis Pedidos)
 */
$tituloPagina = "Mis Pedidos | ElectroHogar";

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
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $p): ?>
                            <tr>
                                <td class="fw-bold text-primary"><?= htmlspecialchars($p['numero_pedido']) ?></td>
                                <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($p['fecha_pedido'])) ?></td>
                                <td><?= $p['total_articulos'] ?> electrodomésticos</td>
                                <td class="small"><?= htmlspecialchars($p['nombre_metodo']) ?></td>
                                <td class="fw-bold">Q <?= number_format($p['total'], 2) ?></td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        <?= htmlspecialchars($p['nombre_estado']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
