<?php
// Vista: Inicio de Sesión
$tituloPagina = "Iniciar Sesión | ElectroHogar";

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/controllers/AuthController.php';

use App\Controllers\AuthController;

$error = null;
$exitoRegistro = !empty($_GET['registrado']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthController();
    $res = $auth->login($_POST['correo'] ?? '', $_POST['password'] ?? '');
    if ($res['success']) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
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
                <div class="text-center mb-4">
                    <i class="bi bi-person-lock display-4 text-primary"></i>
                    <h3 class="fw-bold mt-2">Iniciar Sesión</h3>
                    <p class="text-muted small">Accede a tus compras, favoritos y pedidos</p>
                </div>

                <?php if ($exitoRegistro): ?>
                    <div class="alert alert-success small d-flex align-items-center mb-3">
                        <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                        <span><strong>¡Cuenta creada con éxito!</strong> Ya puedes iniciar sesión.</span>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger small d-flex align-items-center mb-3">
                        <i class="bi bi-exclamation-octagon-fill fs-5 me-2"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Correo Electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="correo" class="form-control" placeholder="ejemplo@correo.com" required value="admin@electrotienda.com">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required value="admin123">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary-app w-100 py-2 fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Ingresar al Sistema
                    </button>
                </form>

                <div class="text-center mt-4 border-top pt-3">
                    <span class="text-muted small">¿No tienes una cuenta aún?</span>
                    <a href="<?= BASE_URL ?>/index.php?ruta=registro" class="small fw-bold text-primary ms-1">Regístrate gratis</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
