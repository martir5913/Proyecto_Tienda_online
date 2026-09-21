<?php
// Vista: Carrito de Compras y Checkout Transaccional
$tituloPagina = "Carrito de Compras | ElectroHogar";
$scriptEspecifico = "carrito.js";

require_once dirname(__DIR__) . '/app/controllers/CarritoController.php';
require_once dirname(__DIR__) . '/config/database.php';

use App\Controllers\CarritoController;
use Config\Database;

$carritoCtrl = new CarritoController();
$resumen = $carritoCtrl->obtenerResumen();

// Token CSRF exclusivo para confirmar pedidos.
if (estaAutenticado() && empty($_SESSION['csrf_checkout'])) {
    $_SESSION['csrf_checkout'] = bin2hex(random_bytes(32));
}

// Obtener únicamente los métodos de pago activos con proyección explícita.
$db = Database::getConnection();
$stmtMetodosPago = $db->prepare(
    "SELECT id_metodo_pago, nombre_metodo FROM metodos_pago WHERE activo = :activo ORDER BY id_metodo_pago ASC"
);
$stmtMetodosPago->bindValue(':activo', 1, PDO::PARAM_INT);
$stmtMetodosPago->execute();
$metodosPago = $stmtMetodosPago->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/layouts/header.php';
?>

<div class="container py-4" id="contenedor-carrito">
    <h3 class="fw-bold mb-4"><i class="bi bi-cart3 me-2"></i>Carrito de Compras</h3>

    <?php if (empty($resumen['items'])): ?>
        <div class="text-center py-5 bg-white rounded-3 shadow-sm" id="carrito-vacio">
            <i class="bi bi-cart-x fs-1 text-muted"></i>
            <h5 class="mt-3 text-muted">Tu carrito está vacío</h5>
            <p class="text-muted small">Explora nuestro catálogo para encontrar electrodomésticos para tu hogar.</p>
            <a href="<?= BASE_URL ?>/index.php?ruta=catalogo" class="btn btn-primary-app">
                <i class="bi bi-grid-fill me-1"></i> Ir al Catálogo
            </a>
        </div>
    <?php else: ?>
        <div class="row g-4" id="carrito-contenido">
            <!-- Tabla de Artículos -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Precio</th>
                                    <th style="width: 150px;" class="text-center">Cantidad</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="lista-items-carrito">
                                <?php foreach ($resumen['items'] as $item): ?>
                                    <tr id="fila-item-<?= $item['id_producto'] ?>" class="item-carrito-row">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-box-seam fs-3 text-muted me-3"></i>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold"><?= htmlspecialchars($item['nombre']) ?></h6>
                                                    <small class="text-muted"><?= htmlspecialchars($item['marca']) ?> | Mod: <?= htmlspecialchars($item['codigo_modelo']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fw-semibold">Q <?= number_format($item['precio'], 2) ?></td>
                                        <td>
                                            <div class="input-group input-group-sm justify-content-center input-group-cantidad" style="width: 110px; margin: 0 auto;">
                                                <button class="btn btn-outline-secondary px-2" type="button" 
                                                        onclick="CarritoModulo.cambiarCantidadRelativa(<?= $item['id_producto'] ?>, -1)">
                                                    <i class="bi bi-dash"></i>
                                                </button>
                                                <input type="number" class="form-control form-control-sm text-center input-cantidad px-1" 
                                                       id="input-cant-<?= $item['id_producto'] ?>"
                                                       value="<?= $item['cantidad'] ?>" min="1" max="99"
                                                       onchange="CarritoModulo.actualizarCantidad(<?= $item['id_producto'] ?>, this.value)">
                                                <button class="btn btn-outline-secondary px-2" type="button" 
                                                        onclick="CarritoModulo.cambiarCantidadRelativa(<?= $item['id_producto'] ?>, 1)">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="fw-bold text-dark" id="subtotal-item-<?= $item['id_producto'] ?>">
                                            Q <?= number_format($item['subtotal'], 2) ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger border-0" 
                                                    onclick="CarritoModulo.eliminarItem(<?= $item['id_producto'] ?>)"
                                                    title="Eliminar artículo">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Resumen y Formulario de Checkout -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Resumen de la Orden</h5>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-semibold" id="resumen-subtotal">Q <?= number_format($resumen['subtotal'], 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">IVA (12%):</span>
                        <span class="fw-semibold" id="resumen-impuesto">Q <?= number_format($resumen['impuesto'], 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Envío Especializado:</span>
                        <span class="text-success fw-semibold">Gratis</span>
                    </div>

                    <div class="d-flex justify-content-between fs-5 fw-bold border-top pt-3 mb-4">
                        <span>Total:</span>
                        <span class="text-primary" id="resumen-total">Q <?= number_format($resumen['total'], 2) ?></span>
                    </div>

                    <?php if (estaAutenticado()): ?>
                        <form onsubmit="CarritoModulo.realizarCheckout(event)">
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= htmlspecialchars($_SESSION['csrf_checkout'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            >

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Dirección de Entrega</label>
                                <textarea name="direccion_envio" class="form-control form-control-sm" rows="2" required maxlength="500"
                                          autocomplete="street-address"
                                          placeholder="Calle, número de casa, zona, municipio..."><?= htmlspecialchars($_SESSION['usuario']['direccion'] ?? '') ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Método de Pago</label>
                                <select name="id_metodo_pago" class="form-select form-select-sm" required>
                                    <?php foreach ($metodosPago as $mp): ?>
                                        <option value="<?= $mp['id_metodo_pago'] ?>"><?= htmlspecialchars($mp['nombre_metodo']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary-app w-100 py-2 fw-bold">
                                <i class="bi bi-shield-check me-2"></i> Confirmar Pedido Transaccional
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning small mb-0">
                            <i class="bi bi-info-circle me-1"></i> Debe <a href="<?= BASE_URL ?>/index.php?ruta=login" class="fw-bold">Iniciar Sesión</a> para completar la compra.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
