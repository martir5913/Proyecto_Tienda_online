<?php
// Vista: Registro de Nuevo Usuario Cliente
$tituloPagina = "Crear Cuenta | Doméstik";

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/controllers/AuthController.php';

use App\Controllers\AuthController;

$mensaje = null;
$tipoMensaje = null;
$datosForm = [
    'nombre'    => '',
    'apellido'  => '',
    'correo'    => '',
    'telefono'  => '',
    'direccion' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datosForm = [
        'nombre'    => trim($_POST['nombre'] ?? ''),
        'apellido'  => trim($_POST['apellido'] ?? ''),
        'correo'    => trim($_POST['correo'] ?? ''),
        'telefono'  => trim($_POST['telefono'] ?? ''),
        'direccion' => trim($_POST['direccion'] ?? '')
    ];

    $auth = new AuthController();
    $res = $auth->registrar($_POST);
    if ($res['success']) {
        // Redirigir de manera automática al login con indicador de éxito
        header('Location: ' . BASE_URL . '/index.php?ruta=login&registrado=1');
        exit;
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
                        <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                        <span><?= htmlspecialchars($mensaje) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" id="form-registro" onsubmit="return validarRegistro(event)">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nombre</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Juan" required value="<?= htmlspecialchars($datosForm['nombre']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Apellido</label>
                            <input type="text" name="apellido" class="form-control" placeholder="Pérez" required value="<?= htmlspecialchars($datosForm['apellido']) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Correo Electrónico</label>
                        <input type="email" name="correo" class="form-control" placeholder="juan.perez@correo.com" required value="<?= htmlspecialchars($datosForm['correo']) ?>">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" id="reg-password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" minlength="6" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Confirmar Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                <input type="password" id="reg-password-confirm" name="password_confirm" class="form-control" placeholder="Repite la contraseña" minlength="6" required>
                            </div>
                        </div>
                    </div>

                    <div id="password-match-feedback" class="small mb-3 d-none"></div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Teléfono de Contacto</label>
                        <input type="tel" name="telefono" class="form-control" placeholder="Ej. 5555-1234" value="<?= htmlspecialchars($datosForm['telefono']) ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Dirección de Entrega</label>
                        <textarea name="direccion" class="form-control" rows="2" placeholder="Zona, calle, número de residencia..."><?= htmlspecialchars($datosForm['direccion']) ?></textarea>
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

<script>
function validarRegistro(event) {
    const pass = document.getElementById('reg-password').value;
    const confirm = document.getElementById('reg-password-confirm').value;
    const feedback = document.getElementById('password-match-feedback');

    if (pass !== confirm) {
        event.preventDefault();
        feedback.className = 'small mb-3 text-danger fw-semibold d-block';
        feedback.innerHTML = '<i class="bi bi-x-circle me-1"></i> Las contraseñas no coinciden.';
        document.getElementById('reg-password-confirm').focus();
        return false;
    }
    return true;
}

document.getElementById('reg-password-confirm').addEventListener('input', function() {
    const pass = document.getElementById('reg-password').value;
    const feedback = document.getElementById('password-match-feedback');

    if (this.value && pass !== this.value) {
        feedback.className = 'small mb-3 text-danger fw-semibold d-block';
        feedback.innerHTML = '<i class="bi bi-x-circle me-1"></i> Las contraseñas no coinciden.';
    } else if (this.value && pass === this.value) {
        feedback.className = 'small mb-3 text-success fw-semibold d-block';
        feedback.innerHTML = '<i class="bi bi-check-circle me-1"></i> Las contraseñas coinciden.';
    } else {
        feedback.className = 'small mb-3 d-none';
    }
});
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
