<?php
/**
 * Vista: Registro de Nuevo Usuario Cliente
 */
$tituloPagina = "Crear Cuenta | ElectroHogar";

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/controllers/AuthController.php';

use App\Controllers\AuthController;

$mensaje = null;
$tipoMensaje = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthController();
    $res = $auth->registrar($_POST);
    if ($res['success']) {
        $mensaje = $res['message'] . ' Ya puedes iniciar sesión.';
        $tipoMensaje = 'success';
    } else {
        $mensaje = $res['message'];
        $tipoMensaje = 'danger';
    }
}

require_once __DIR__ . '/layouts/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="bi bi-person-plus display-4 text-primary"></i>
                    <h3 class="fw-bold mt-2">Crear Cuenta</h3>
                    <p class="text-muted small">Regístrate para comprar electrodomésticos con garantía</p>
                </div>

                <?php if ($mensaje): ?>
                    <div class="alert alert-<?= $tipoMensaje ?> small d-flex align-items-center mb-3">
                        <i class="bi bi-info-circle-fill fs-5 me-2"></i>
                        <span><?= htmlspecialchars($mensaje) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nombre</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Juan" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Apellido</label>
                            <input type="text" name="apellido" class="form-control" placeholder="Pérez" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Correo Electrónico</label>
                        <input type="email" name="correo" class="form-control" placeholder="juan.perez@correo.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Contraseña</label>
                        <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Teléfono de Contacto</label>
                        <input type="tel" name="telefono" class="form-control" placeholder="Ej. 5555-1234">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Dirección de Entrega</label>
                        <textarea name="direccion" class="form-control" rows="2" placeholder="Zona, calle, número de residencia..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary-app w-100 py-2 fw-semibold">
                        <i class="bi bi-check2-circle me-2"></i> Completar Registro
                    </button>
                </form>

                <div class="text-center mt-4 border-top pt-3">
                    <span class="text-muted small">¿Ya tienes cuenta?</span>
                    <a href="<?= BASE_URL ?>/index.php?ruta=login" class="small fw-bold text-primary ms-1">Inicia sesión</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
