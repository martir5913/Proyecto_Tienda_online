<?php

declare(strict_types=1);

// Vista: Restablecer Contraseña
$tituloPagina = "Restablecer Contraseña | Doméstik";

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/controllers/AuthController.php';

use App\Controllers\AuthController;

$token = (string)($_GET['token'] ?? $_POST['token'] ?? '');
$auth = new AuthController();
$usuario = $token !== '' ? $auth->validarTokenRecuperacion($token) : null;

$error = null;
$exito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nueva = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['password_confirm'] ?? '');

    $res = $auth->restablecerPasswordConToken($token, $nueva, $confirm);
    if ($res['success']) {
        $exito = true;
    } else {
        $error = $res['message'];
    }
}

require_once __DIR__ . '/layouts/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                
                <?php if ($exito): ?>
                    <div class="text-center py-3">
                        <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle shadow mb-3" style="width: 64px; height: 64px;">
                            <i class="bi bi-check-lg display-5"></i>
                        </div>
                        <h4 class="fw-bold mb-2">¡Contraseña Actualizada!</h4>
                        <p class="text-muted small mb-4">Tu contraseña ha sido restablecida exitosamente. Ahora puedes ingresar con tus nuevas credenciales.</p>
                        <a href="<?= BASE_URL ?>/index.php?ruta=login" class="btn btn-primary-app w-100 py-2 fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Iniciar Sesión
                        </a>
                    </div>
                <?php elseif (!$usuario): ?>
                    <div class="text-center py-3">
                        <div class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-circle mb-3" style="width: 64px; height: 64px;">
                            <i class="bi bi-shield-x fs-2"></i>
                        </div>
                        <h4 class="fw-bold text-danger mb-2">Enlace no válido o expirado</h4>
                        <p class="text-muted small mb-4">Por razones de seguridad, los enlaces de recuperación tienen un tiempo límite de uso (60 minutos) y son de un solo uso.</p>
                        <a href="<?= BASE_URL ?>/index.php?ruta=recuperar_password" class="btn btn-outline-primary w-100 py-2 fw-semibold mb-2">
                            <i class="bi bi-arrow-clockwise me-2"></i> Solicitar un nuevo enlace
                        </a>
                        <a href="<?= BASE_URL ?>/index.php?ruta=login" class="btn btn-link text-decoration-none small text-muted">
                            Volver al inicio de sesión
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-3" style="width: 64px; height: 64px;">
                            <i class="bi bi-shield-lock fs-2"></i>
                        </div>
                        <h3 class="fw-bold">Nueva Contraseña</h3>
                        <p class="text-muted small">Crea una nueva contraseña segura para la cuenta de <strong><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></strong>.</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger small d-flex align-items-center mb-3">
                            <i class="bi bi-exclamation-octagon-fill fs-5 me-2 flex-shrink-0"></i>
                            <span><?= htmlspecialchars($error) ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                        <div class="mb-3">
                            <label for="password" class="form-label small fw-semibold">Nueva Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                <input type="password" id="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required minlength="6" autocomplete="new-password" autofocus>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirm" class="form-label small fw-semibold">Confirmar Nueva Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-shield-check"></i></span>
                                <input type="password" id="password_confirm" name="password_confirm" class="form-control" placeholder="Repite tu contraseña" required minlength="6" autocomplete="new-password">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-app w-100 py-2 fw-semibold">
                            <i class="bi bi-check2-circle me-2"></i> Guardar Nueva Contraseña
                        </button>
                    </form>

                    <div class="text-center border-top pt-3 mt-4">
                        <a href="<?= BASE_URL ?>/index.php?ruta=login" class="small text-decoration-none text-muted">
                            <i class="bi bi-arrow-left me-1"></i> Cancelar y volver
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
