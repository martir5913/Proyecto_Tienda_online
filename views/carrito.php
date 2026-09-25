<?php
// Vista: Carrito de Compras y Checkout Transaccional
$tituloPagina = "Carrito de Compras | Doméstik";
$scriptEspecifico = "carrito.js";

if (!class_exists('App\Controllers\CarritoController')) {
    require_once dirname(__DIR__) . '/app/controllers/CarritoController.php';
}
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
                        <form onsubmit="CarritoModulo.realizarCheckout(event)" id="form-checkout">
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= htmlspecialchars($_SESSION['csrf_checkout'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            >

                            <!-- Dirección de Entrega -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">
                                    <i class="bi bi-geo-alt-fill text-primary me-1"></i> Dirección de Entrega
                                </label>
                                <textarea name="direccion_envio" class="form-control form-control-sm" rows="2" required maxlength="500"
                                          autocomplete="street-address"
                                          placeholder="Calle, número de casa, zona, municipio..."><?= htmlspecialchars($_SESSION['usuario']['direccion'] ?? '') ?></textarea>
                            </div>

                            <!-- Métodos de Pago (RF13) -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold d-block">
                                    <i class="bi bi-credit-card-fill text-primary me-1"></i> Método de Pago
                                </label>
                                <div class="d-flex flex-column gap-2 mb-2">
                                    <?php foreach ($metodosPago as $idx => $mp): ?>
                                        <div class="form-check p-2 border rounded-3 bg-light bg-opacity-50">
                                            <input class="form-check-input ms-1 me-2" type="radio" name="id_metodo_pago" 
                                                   id="mp-<?= $mp['id_metodo_pago'] ?>" 
                                                   value="<?= $mp['id_metodo_pago'] ?>" 
                                                   <?= $idx === 0 ? 'checked' : '' ?>
                                                   onchange="CarritoModulo.cambiarMetodoPago(<?= $mp['id_metodo_pago'] ?>)">
                                            <label class="form-check-label small fw-semibold d-flex justify-content-between align-items-center w-100 pe-2" for="mp-<?= $mp['id_metodo_pago'] ?>">
                                                <span><?= htmlspecialchars($mp['nombre_metodo']) ?></span>
                                                <?php if (str_contains(strtolower($mp['nombre_metodo']), 'tarjeta')): ?>
                                                    <span>
                                                        <i class="bi bi-credit-card-2-front text-primary"></i>
                                                    </span>
                                                <?php elseif (str_contains(strtolower($mp['nombre_metodo']), 'transferencia')): ?>
                                                    <span>
                                                        <i class="bi bi-bank text-success"></i>
                                                    </span>
                                                <?php else: ?>
                                                    <span>
                                                        <i class="bi bi-cash-coin text-warning"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Panel Dinámico 1: Tarjeta de Crédito/Débito -->
                                <div id="panel-pago-tarjeta" class="p-3 border rounded-3 bg-white mb-2 shadow-sm">
                                    <h6 class="small fw-bold mb-2 text-primary"><i class="bi bi-lock-fill me-1"></i>Pago Seguro con Tarjeta</h6>
                                    <div class="mb-2">
                                        <label class="extra-small text-muted fw-semibold">Nombre del Titular</label>
                                        <input type="text" name="card_nombre" id="card_nombre" class="form-control form-control-sm" 
                                               placeholder="Como aparece en el plástico" value="<?= htmlspecialchars($_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellido']) ?>">
                                    </div>
                                    <div class="mb-2">
                                        <label class="extra-small text-muted fw-semibold">Número de Tarjeta</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                                            <input type="text" name="card_numero" id="card_numero" class="form-control" 
                                                   placeholder="4000 1234 5678 9010" maxlength="19">
                                        </div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="extra-small text-muted fw-semibold">Vencimiento</label>
                                            <input type="text" name="card_exp" id="card_exp" class="form-control form-control-sm" placeholder="MM/AA" maxlength="5">
                                        </div>
                                        <div class="col-6">
                                            <label class="extra-small text-muted fw-semibold">CVV</label>
                                            <input type="password" name="card_cvv" id="card_cvv" class="form-control form-control-sm" placeholder="123" maxlength="4">
                                        </div>
                                    </div>
                                </div>

                                <!-- Panel Dinámico 2: Transferencia Bancaria -->
                                <div id="panel-pago-transferencia" class="p-3 border rounded-3 bg-white mb-2 shadow-sm d-none">
                                    <h6 class="small fw-bold mb-2 text-success"><i class="bi bi-bank me-1"></i>Cuentas Bancarias Doméstik</h6>
                                    <p class="extra-small text-muted mb-2">
                                        <strong>Banco Industrial:</strong> Monetaria 001-928374-1<br>
                                        <strong>BAC Credomatic:</strong> Monetaria 90-283746-5<br>
                                        <em>A nombre de: Doméstik S.A.</em>
                                    </p>
                                    <label class="extra-small text-muted fw-semibold">No. de Boleta o Transferencia</label>
                                    <input type="text" name="numero_boleta" id="numero_boleta" class="form-control form-control-sm" placeholder="Ej. TRANS-8492048">
                                </div>

                                <!-- Panel Dinámico 3: Contra Entrega -->
                                <div id="panel-pago-entrega" class="p-3 border rounded-3 bg-white mb-2 shadow-sm d-none">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-truck text-warning fs-3 me-2"></i>
                                        <div>
                                            <h6 class="small fw-bold mb-0">Pago al Recibir</h6>
                                            <small class="text-muted extra-small">Paga en efectivo o tarjeta con POS inalámbrico al momento de la entrega.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Datos de Facturación (RF20 / Facturación) -->
                            <div class="mb-3 p-3 bg-light rounded-3 border">
                                <h6 class="small fw-bold mb-2 d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-receipt me-1 text-primary"></i>Datos de Facturación</span>
                                    <span class="badge bg-secondary-subtle text-dark extra-small">IVA 12% Incluido</span>
                                </h6>
                                <div class="row g-2 mb-2">
                                    <div class="col-5">
                                        <label class="extra-small text-muted fw-semibold">NIT</label>
                                        <input type="text" name="nit" id="fact_nit" class="form-control form-control-sm" value="C/F" placeholder="C/F o NIT">
                                    </div>
                                    <div class="col-7">
                                        <label class="extra-small text-muted fw-semibold">Nombre / Razón Social</label>
                                        <input type="text" name="facturar_a" id="fact_nombre" class="form-control form-control-sm" 
                                               value="<?= htmlspecialchars($_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellido']) ?>">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary-app w-100 py-2 fw-bold shadow-sm" id="btn-confirmar-checkout">
                                <i class="bi bi-shield-check me-2"></i> Confirmar y Pagar Orden
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
