<?php

declare(strict_types=1);

// Vista: Recuperar Contraseña
$tituloPagina = "Recuperar Contraseña | Doméstik";

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/controllers/AuthController.php';

use App\Controllers\AuthController;

$mensaje = null;
$tipoMensaje = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = (string)($_POST['correo'] ?? '');
    $auth = new AuthController();
    $res = $auth->solicitarRecuperacion($correo);
    $mensaje = $res['message'];
    $tipoMensaje = $res['success'] ? 'success' : 'danger';
}

require_once __DIR__ . '/layouts/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-key-fill fs-2"></i>
                    </div>
                    <h3 class="fw-bold">Recuperar Contraseña</h3>
                    <p class="text-muted small">Ingresa el correo electrónico asociado a tu cuenta para enviarte las instrucciones de restablecimiento.</p>
                </div>

                <?php if ($mensaje): ?>
                    <div class="alert alert-<?= $tipoMensaje ?> small d-flex align-items-center mb-4 shadow-sm" role="alert">
                        <i class="bi bi-<?= $tipoMensaje === 'success' ? 'check-circle-fill' : 'exclamation-octagon-fill' ?> fs-5 me-2 flex-shrink-0"></i>
                        <div><?= htmlspecialchars($mensaje) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-4">
                        <label for="correo" class="form-label small fw-semibold">Correo Electrónico Registrado</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                            <input type="email" id="correo" name="correo" class="form-control" placeholder="ejemplo@correo.com" required autocomplete="email" autofocus>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary-app w-100 py-2 fw-semibold mb-3">
                        <i class="bi bi-send-check me-2"></i> Enviar Enlace de Recuperación
                    </button>
                </form>

                <div class="text-center border-top pt-3">
                    <a href="<?= BASE_URL ?>/index.php?ruta=login" class="small text-decoration-none text-muted">
                        <i class="bi bi-arrow-left me-1"></i> Volver a Iniciar Sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
