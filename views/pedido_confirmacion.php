<?php

declare(strict_types=1);

$tituloPagina = "Confirmación de Pedido | Doméstik";

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/middlewares/AuthMiddleware.php';
require_once dirname(__DIR__) . '/app/controllers/PedidoController.php';

use App\Middlewares\AuthMiddleware;
use App\Controllers\PedidoController;

AuthMiddleware::verificarAutenticado();

$idUsuario = (int)$_SESSION['usuario']['id_usuario'];
$idPedido = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

if ($idPedido <= 0) {
    header('Location: ' . BASE_URL . '/index.php?ruta=mis_pedidos');
    exit;
}

$pedidoCtrl = new PedidoController();
$pedido = $pedidoCtrl->getDetalle((int)$idPedido, $idUsuario, esAdmin());

if (!$pedido) {
    header('Location: ' . BASE_URL . '/index.php?ruta=mis_pedidos');
    exit;
}

require_once __DIR__ . '/layouts/header.php';
?>

<div class="container py-5">
    <!-- Tarjeta Principal de Éxito -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-body p-4 p-md-5 text-center bg-light border-bottom">
            <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle shadow mb-3" style="width: 72px; height: 72px;">
                <i class="bi bi-check-lg display-5"></i>
            </div>
            <h2 class="fw-bold text-dark mb-1">¡Gracias por tu compra!</h2>
            <p class="text-muted mb-3">Tu pedido ha sido registrado y procesado exitosamente en nuestro sistema.</p>
            <div class="d-inline-block bg-white px-3 py-2 rounded-pill border shadow-sm">
                <span class="text-muted small">No. de Pedido: </span>
                <strong class="text-primary fs-6"><?= htmlspecialchars($pedido['numero_pedido']) ?></strong>
            </div>
        </div>

        <!-- Alerta de Notificación / Envío de Confirmación (RF20) -->
        <div class="bg-primary-subtle border-bottom px-4 py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center">
                <i class="bi bi-envelope-check-fill text-primary fs-4 me-3"></i>
                <div>
                    <strong class="text-primary d-block">Confirmación de compra enviada</strong>
                    <small class="text-muted">Hemos enviado el recibo digital y detalles de envío al correo: <strong><?= htmlspecialchars($pedido['correo_usuario']) ?></strong></small>
                </div>
            </div>
            <span class="badge bg-success px-3 py-2 rounded-pill">
                <i class="bi bi-shield-check me-1"></i> Transacción Verificada
            </span>
        </div>

        <div class="card-body p-4 p-md-5">
            <!-- Stepper de Estado del Pedido -->
            <div class="mb-5">
                <h6 class="text-muted small fw-bold text-uppercase mb-3">Estado del Pedido</h6>
                <div class="row text-center g-2 position-relative">
                    <?php
                    $idEstado = (int)$pedido['id_estado_pedido'];
                    $estados = [
                        1 => ['nombre' => 'Pendiente', 'icono' => 'bi-hourglass-split'],
                        2 => ['nombre' => 'Procesando', 'icono' => 'bi-gear-wide-connected'],
                        3 => ['nombre' => 'Enviado', 'icono' => 'bi-truck'],
                        4 => ['nombre' => 'Entregado', 'icono' => 'bi-box-seam-fill']
                    ];
                    ?>
                    <?php foreach ($estados as $num => $est): ?>
                        <?php
                        $completado = $idEstado >= $num;
                        $activo = $idEstado === $num;
                        $colorClase = $completado ? ($activo ? 'bg-primary text-white border-primary shadow-sm' : 'bg-success text-white border-success') : 'bg-light text-muted border-secondary-subtle';
                        ?>
                        <div class="col-3">
                            <div class="p-3 rounded-3 border <?= $colorClase ?>">
                                <i class="bi <?= $est['icono'] ?> fs-4 d-block mb-1"></i>
                                <span class="extra-small fw-semibold d-block"><?= $est['nombre'] ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Resumen de Productos Comprados -->
            <h5 class="fw-bold mb-3 border-bottom pb-2">
                <i class="bi bi-boxes me-2 text-primary"></i>Artículos en esta Orden
            </h5>
            <div class="table-responsive mb-4">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-end">Precio Unitario</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedido['items'] as $it): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3 p-1 border rounded bg-white" style="width: 54px; height: 54px; display: flex; align-items: center; justify-content: center;">
                                            <?php if (!empty($it['imagen']) && file_exists(BASE_DIR . '/' . $it['imagen'])): ?>
                                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($it['imagen']) ?>" alt="<?= htmlspecialchars($it['nombre_producto']) ?>" class="img-fluid" style="max-height: 46px; object-fit: contain;">
                                            <?php else: ?>
                                                <i class="bi bi-box-seam text-muted fs-4"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold"><?= htmlspecialchars($it['nombre_producto']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($it['nombre_marca']) ?> · Mod: <?= htmlspecialchars($it['codigo_modelo']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center fw-semibold"><?= (int)$it['cantidad'] ?></td>
                                <td class="text-end">Q <?= number_format((float)$it['precio_unitario'], 2) ?></td>
                                <td class="text-end fw-bold text-dark">Q <?= number_format((float)$it['subtotal'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row g-4">
                <!-- Columna Izquierda: Entrega y Pago -->
                <div class="col-md-7">
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <h6 class="fw-bold mb-2 text-primary"><i class="bi bi-geo-alt me-1"></i>Información de Entrega</h6>
                        <p class="small text-muted mb-1"><strong>Destinatario:</strong> <?= htmlspecialchars($pedido['nombre_usuario'] . ' ' . $pedido['apellido_usuario']) ?></p>
                        <p class="small text-muted mb-1"><strong>Teléfono:</strong> <?= htmlspecialchars($pedido['telefono_usuario'] ?: 'No registrado') ?></p>
                        <p class="small text-muted mb-0"><strong>Dirección:</strong> <?= nl2br(htmlspecialchars($pedido['direccion_envio'])) ?></p>
                    </div>

                    <div class="p-3 bg-light rounded-3 border">
                        <h6 class="fw-bold mb-2 text-primary"><i class="bi bi-credit-card me-1"></i>Método de Pago y Facturación</h6>
                        <p class="small text-muted mb-1"><strong>Forma de Pago:</strong> <?= htmlspecialchars($pedido['nombre_metodo']) ?></p>
                        <?php if (!empty($pedido['notas'])): ?>
                            <p class="small text-muted mb-0"><strong>Detalles:</strong> <?= htmlspecialchars($pedido['notas']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Columna Derecha: Totales Financieros -->
                <div class="col-md-5">
                    <div class="card border bg-white p-3 rounded-3 shadow-sm">
                        <h6 class="fw-bold mb-3 border-bottom pb-2">Resumen Financiero</h6>
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Subtotal:</span>
                            <span class="fw-semibold">Q <?= number_format((float)$pedido['subtotal'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">IVA (12%):</span>
                            <span class="fw-semibold">Q <?= number_format((float)$pedido['impuesto'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Envío Especializado:</span>
                            <span class="text-success fw-semibold">Gratis</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between fs-5 fw-bold text-dark">
                            <span>Total Pagado:</span>
                            <span class="text-primary">Q <?= number_format((float)$pedido['total'], 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-5 pt-3 border-top">
                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>/index.php?ruta=factura&id=<?= $pedido['id_pedido'] ?>" class="btn btn-primary-app px-4 py-2" target="_blank">
                        <i class="bi bi-printer me-2"></i> Ver / Imprimir Factura Digital
                    </a>
                    <a href="<?= BASE_URL ?>/index.php?ruta=mis_pedidos" class="btn btn-outline-secondary px-3 py-2">
                        <i class="bi bi-bag-check me-1"></i> Ir a Mis Pedidos
                    </a>
                </div>
                <a href="<?= BASE_URL ?>/index.php?ruta=catalogo" class="btn btn-link text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i> Seguir Comprando en el Catálogo
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
