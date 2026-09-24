<?php

declare(strict_types=1);

$tituloPagina = 'Validador de Plantilla de Correo | Doméstik';

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/models/Model.php';
require_once dirname(__DIR__, 2) . '/app/models/PedidoAdmin.php';
require_once dirname(__DIR__, 2) . '/app/services/EmailService.php';
require_once dirname(__DIR__, 2) . '/app/middlewares/AuthMiddleware.php';

use App\Middlewares\AuthMiddleware;
use App\Models\PedidoAdmin;
use App\Services\EmailService;

AuthMiddleware::verificarAdmin();

$emailService = new EmailService();
$pedidoAdminModel = new PedidoAdmin();

// Obtener lista de pedidos recientes para previsualizar
$pedidosDisponibles = $pedidoAdminModel->obtenerPedidos(['orden' => 'recientes']);

// Determinar el pedido a mostrar (por defecto el demo)
$idPedidoSeleccionado = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

$mensajeEnvioPrueba = null;
$tipoMensaje = 'info';

// Procesar envío de correo de prueba si se solicitó por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_prueba'])) {
    $emailDestino = filter_input(INPUT_POST, 'correo_destino', FILTER_VALIDATE_EMAIL);
    if ($emailDestino) {
        $pedidoActual = ($idPedidoSeleccionado > 0)
            ? ($pedidoAdminModel->obtenerDetalle($idPedidoSeleccionado) ?: $emailService->obtenerPedidoMuestra())
            : $emailService->obtenerPedidoMuestra();

        $resp = $emailService->enviarCorreoPrueba((string)$emailDestino, $pedidoActual);
        $mensajeEnvioPrueba = $resp['message'];
        $tipoMensaje = $resp['success'] ? 'success' : 'danger';
    } else {
        $mensajeEnvioPrueba = 'Por favor ingresa un correo electrónico válido.';
        $tipoMensaje = 'warning';
    }
}

// Si se pide solo el contenido HTML aislado (para el iframe)
if (isset($_GET['modo']) && $_GET['modo'] === 'raw') {
    $pedidoActual = ($idPedidoSeleccionado > 0)
        ? ($pedidoAdminModel->obtenerDetalle($idPedidoSeleccionado) ?: $emailService->obtenerPedidoMuestra())
        : $emailService->obtenerPedidoMuestra();

    header('Content-Type: text/html; charset=utf-8');
    echo $emailService->obtenerHtmlPlantilla($pedidoActual);
    exit;
}

$pedidoParaMostrar = ($idPedidoSeleccionado > 0)
    ? ($pedidoAdminModel->obtenerDetalle($idPedidoSeleccionado) ?: $emailService->obtenerPedidoMuestra())
    : $emailService->obtenerPedidoMuestra();

$htmlPlantilla = $emailService->obtenerHtmlPlantilla($pedidoParaMostrar);

require_once dirname(__DIR__) . '/layouts/header.php';
?>

<div class="container-fluid py-4 px-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-envelope-paper-heart me-2 text-primary"></i>Validador y Vista Previa de Plantilla de Correo
            </h3>
            <p class="text-muted small mb-0">
                Inspecciona y valida la confirmación de compras generada por PHPMailer antes y después de enviarse al cliente.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/index.php?ruta=admin_pedidos" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-receipt-cutoff me-1"></i> Ir a Gestión de Pedidos
            </a>
            <a href="<?= BASE_URL ?>/index.php?ruta=admin_dashboard" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <?php if ($mensajeEnvioPrueba): ?>
        <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-<?= $tipoMensaje === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?> me-2"></i>
            <?= htmlspecialchars($mensajeEnvioPrueba) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Panel Izquierdo: Controles y Pruebas -->
        <div class="col-lg-4 col-xl-3">
            <!-- Selector de Origen de Datos -->
            <div class="card border-0 shadow-sm rounded-3 mb-3">
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-database me-2 text-primary"></i>Origen de Datos</h6>
                    <form method="GET" action="<?= BASE_URL ?>/index.php">
                        <input type="hidden" name="ruta" value="admin_preview_email">
                        <div class="mb-3">
                            <label for="selector-pedido" class="form-label small text-muted">Selecciona una Orden:</label>
                            <select class="form-select form-select-sm" id="selector-pedido" name="id" onchange="this.form.submit()">
                                <option value="0" <?= $idPedidoSeleccionado === 0 ? 'selected' : '' ?>>
                                    Datos de Muestra (Demo Completo)
                                </option>
                                <optgroup label="Órdenes Reales en BD">
                                    <?php foreach ($pedidosDisponibles as $ped): ?>
                                        <option value="<?= (int)$ped['id_pedido'] ?>" <?= $idPedidoSeleccionado === (int)$ped['id_pedido'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($ped['numero_pedido']) ?> - <?= htmlspecialchars($ped['nombre'] . ' ' . $ped['apellido']) ?> (Q <?= number_format((float)$ped['total'], 2) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Formulario para Enviar Correo de Prueba en Vivo -->
            <div class="card border-0 shadow-sm rounded-3 mb-3 bg-light">
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-send-check me-2 text-success"></i>Enviar Prueba en Vivo</h6>
                    <p class="extra-small text-muted mb-3">Envía esta plantilla al instante a tu correo personal para verificar compatibilidad.</p>
                    <form method="POST" action="">
                        <input type="hidden" name="enviar_prueba" value="1">
                        <div class="mb-3">
                            <label for="correo_destino" class="form-label small text-muted">Correo Destinatario:</label>
                            <input type="email" class="form-control form-control-sm" id="correo_destino" name="correo_destino" placeholder="ejemplo@gmail.com" required value="<?= htmlspecialchars($_SESSION['usuario']['correo'] ?? '') ?>">
                        </div>
                        <button type="submit" class="btn btn-primary-app btn-sm w-100">
                            <i class="bi bi-envelope-arrow-up me-1"></i> Enviar Correo de Prueba
                        </button>
                    </form>
                </div>
            </div>

            <!-- Información de Configuración SMTP Activa -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-gear-wide-connected me-2 text-secondary"></i>Configuración SMTP (.env)</h6>
                    <ul class="list-unstyled extra-small mb-0">
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Servidor:</span>
                            <strong><?= htmlspecialchars((string)env('MAIL_HOST', 'No definido')) ?></strong>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Puerto:</span>
                            <strong><?= htmlspecialchars((string)env('MAIL_PORT', '587')) ?></strong>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Cifrado:</span>
                            <span class="badge bg-info text-dark"><?= strtoupper((string)env('MAIL_ENCRYPTION', 'TLS')) ?></span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Usuario Emisor:</span>
                            <strong class="text-truncate" style="max-width: 140px;"><?= htmlspecialchars((string)env('MAIL_USER', 'No configurado')) ?></strong>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Nombre Emisor:</span>
                            <span class="text-truncate" style="max-width: 140px;"><?= htmlspecialchars((string)env('MAIL_FROM_NAME', 'ElectroHogar')) ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Panel Derecho: Visor Interactivo y Responsive -->
        <div class="col-lg-8 col-xl-9">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2 rounded-pill">
                            <i class="bi bi-eye me-1"></i> Visualización en Vivo
                        </span>
                        <span class="text-muted small">
                            Orden: <strong><?= htmlspecialchars($pedidoParaMostrar['numero_pedido']) ?></strong>
                        </span>
                    </div>

                    <!-- Botones de Tamaño / Dispositivo -->
                    <div class="btn-group btn-group-sm" role="group" aria-label="Dispositivos">
                        <button type="button" class="btn btn-outline-secondary active" id="btn-vista-desktop" onclick="cambiarDispositivo('100%', this)">
                            <i class="bi bi-laptop me-1"></i> Escritorio
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btn-vista-tablet" onclick="cambiarDispositivo('620px', this)">
                            <i class="bi bi-tablet me-1"></i> Tablet (620px)
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btn-vista-movil" onclick="cambiarDispositivo('380px', this)">
                            <i class="bi bi-phone me-1"></i> Móvil (380px)
                        </button>
                        <a href="<?= BASE_URL ?>/index.php?ruta=admin_preview_email&id=<?= $idPedidoSeleccionado ?>&modo=raw" target="_blank" class="btn btn-outline-primary" title="Abrir en pestaña completa">
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Contenedor del Iframe con scroll y fondo neutro -->
                <div class="card-body p-0 bg-dark-subtle d-flex justify-content-center align-items-center" style="min-height: 680px; overflow-x: auto;">
                    <div id="contenedor-iframe" style="width: 100%; transition: width 0.3s ease; display: flex; justify-content: center; padding: 20px 0;">
                        <iframe
                            id="iframe-preview"
                            src="<?= BASE_URL ?>/index.php?ruta=admin_preview_email&id=<?= $idPedidoSeleccionado ?>&modo=raw"
                            style="width: 100%; height: 750px; border: none; background: #ffffff; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15);"
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cambiarDispositivo(ancho, boton) {
    const contenedor = document.getElementById('contenedor-iframe');
    const iframe = document.getElementById('iframe-preview');
    
    if (ancho === '100%') {
        contenedor.style.maxWidth = '100%';
        iframe.style.maxWidth = '100%';
    } else {
        contenedor.style.maxWidth = ancho;
        iframe.style.maxWidth = ancho;
    }

    document.querySelectorAll('.btn-group button').forEach(b => b.classList.remove('active'));
    boton.classList.add('active');
}
</script>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>
