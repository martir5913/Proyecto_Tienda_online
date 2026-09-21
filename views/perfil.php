<?php

declare(strict_types=1);

$tituloPagina = "Mi Perfil | Doméstik";

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/models/Model.php';
require_once dirname(__DIR__) . '/app/models/Usuario.php';
require_once dirname(__DIR__) . '/app/controllers/AuthController.php';

use App\Models\Usuario;
use App\Controllers\AuthController;

if (!estaAutenticado()) {
    header('Location: ' . BASE_URL . '/index.php?ruta=login');
    exit;
}

$idUsuario = (int)$_SESSION['usuario']['id_usuario'];
$usuarioModel = new Usuario();
$authCtrl = new AuthController();

// Protección CSRF
if (empty($_SESSION['csrf_perfil'])) {
    $_SESSION['csrf_perfil'] = bin2hex(random_bytes(32));
}
$csrfToken = (string)$_SESSION['csrf_perfil'];

$mensajePerfil = null;
$tipoPerfil = null;
$mensajePass = null;
$tipoPass = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenRecibido = (string)($_POST['csrf_token'] ?? '');
    
    if (empty($tokenRecibido) || !hash_equals((string)($_SESSION['csrf_perfil'] ?? ''), $tokenRecibido)) {
        $mensajePerfil = 'Token de seguridad inválido o expirado. Por favor, recarga la página.';
        $tipoPerfil = 'danger';
        $mensajePass = 'Token de seguridad inválido o expirado. Por favor, recarga la página.';
        $tipoPass = 'danger';
    } else {
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'guardar_perfil') {
            $res = $authCtrl->actualizarPerfil($idUsuario, $_POST);
            $mensajePerfil = $res['message'];
            $tipoPerfil = $res['success'] ? 'success' : 'danger';
        }

        if ($accion === 'cambiar_password') {
            $actual = (string)($_POST['password_actual'] ?? '');
            $nueva = (string)($_POST['password_nueva'] ?? '');
            $confirm = (string)($_POST['password_confirm'] ?? '');

            $res = $authCtrl->cambiarPassword($idUsuario, $actual, $nueva, $confirm);
            $mensajePass = $res['message'];
            $tipoPass = $res['success'] ? 'success' : 'danger';
        }
    }
}

$usuario = $usuarioModel->obtenerPorId($idUsuario) ?? [];
$nombreUsuario = trim((string)($usuario['nombre'] ?? 'U'));
$apellidoUsuario = trim((string)($usuario['apellido'] ?? ''));
$ini1 = mb_substr($nombreUsuario, 0, 1, 'UTF-8');
$ini2 = mb_substr($apellidoUsuario, 0, 1, 'UTF-8');
$iniciales = mb_strtoupper($ini1 . $ini2, 'UTF-8');

require_once __DIR__ . '/layouts/header.php';
?>

<div class="container py-5">
    <!-- Encabezado -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-person-gear me-2 text-primary"></i>Mi Cuenta y Perfil</h3>
            <p class="text-muted small mb-0">Gestiona tus datos personales, direcciones habituales de entrega y seguridad.</p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="<?= BASE_URL ?>/index.php?ruta=mis_pedidos" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-bag-check me-1"></i> Mis Pedidos
            </a>
            <a href="<?= BASE_URL ?>/index.php?ruta=wishlist" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-heart me-1"></i> Favoritos
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Columna Lateral: Tarjeta Resumen -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 text-center p-4 mb-4">
                <div class="d-flex justify-content-center mb-3">
                    <div class="rounded-circle bg-primary text-white display-6 fw-bold d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px;">
                        <?= htmlspecialchars($iniciales, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>
                <h5 class="fw-bold mb-1"><?= htmlspecialchars(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')) ?></h5>
                <p class="text-muted small mb-2"><?= htmlspecialchars($usuario['correo'] ?? '') ?></p>

                <div class="d-flex justify-content-center gap-2 mb-3">
                    <span class="badge bg-primary-subtle text-primary border px-2 py-1">
                        <i class="bi bi-shield-check me-1"></i> <?= htmlspecialchars($usuario['nombre_rol'] ?? 'Cliente') ?>
                    </span>
                    <span class="badge bg-success-subtle text-success px-2 py-1">
                        <i class="bi bi-check-circle me-1"></i> <?= htmlspecialchars($usuario['estado'] ?? 'Activo') ?>
                    </span>
                </div>

                <hr class="my-3">

                <div class="row text-center g-2">
                    <div class="col-6">
                        <div class="p-2 bg-light rounded-3">
                            <small class="text-muted d-block">Pedidos</small>
                            <span class="fw-bold fs-5 text-dark"><?= (int)($usuario['total_pedidos'] ?? 0) ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 bg-light rounded-3">
                            <small class="text-muted d-block">Total Comprado</small>
                            <span class="fw-bold fs-6 text-primary">Q <?= number_format((float)($usuario['total_gastado'] ?? 0), 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información sobre Métodos de Pago Habilitados -->
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-credit-card me-2 text-primary"></i>Métodos de Pago Aceptados</h6>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2 d-flex align-items-center">
                        <i class="bi bi-credit-card-2-front text-success fs-5 me-2"></i>
                        <span>Tarjetas de Crédito y Débito (Visa, Mastercard)</span>
                    </li>
                    <li class="mb-2 d-flex align-items-center">
                        <i class="bi bi-bank text-primary fs-5 me-2"></i>
                        <span>Transferencias Bancarias Inmediatas</span>
                    </li>
                    <li class="d-flex align-items-center">
                        <i class="bi bi-cash-coin text-warning fs-5 me-2"></i>
                        <span>Pago Contra Entrega en efectivo o POS</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Columna Principal: Formularios de Edición -->
        <div class="col-lg-8">
            <!-- Formulario 1: Datos Personales y Dirección -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2">
                    <i class="bi bi-person-lines-fill me-2 text-primary"></i>Datos Personales y Dirección de Entrega
                </h5>

                <?php if ($mensajePerfil): ?>
                    <div class="alert alert-<?= $tipoPerfil ?> small d-flex align-items-center mb-3">
                        <i class="bi bi-info-circle-fill fs-5 me-2"></i>
                        <span><?= htmlspecialchars($mensajePerfil) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="accion" value="guardar_perfil">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nombre</label>
                            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Apellido</label>
                            <input type="text" name="apellido" class="form-control" value="<?= htmlspecialchars($usuario['apellido'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Correo Electrónico</label>
                            <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($usuario['correo'] ?? '') ?>" readonly>
                            <small class="text-muted extra-small">El correo electrónico es el identificador principal de tu cuenta.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Teléfono de Contacto</label>
                            <input type="tel" name="telefono" class="form-control" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>" placeholder="Ej. 5555-1234">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Dirección Habitual de Entrega</label>
                        <textarea name="direccion" class="form-control" rows="3" placeholder="Calle, número de casa, colonia, zona, municipio y referencias..."><?= htmlspecialchars($usuario['direccion'] ?? '') ?></textarea>
                        <small class="text-muted extra-small">Esta dirección se utilizará de forma automática al confirmar tus compras en el carrito.</small>
                    </div>

                    <button type="submit" class="btn btn-primary-app px-4 py-2 fw-semibold">
                        <i class="bi bi-save me-1"></i> Guardar Información
                    </button>
                </form>
            </div>

            <!-- Formulario 2: Seguridad y Cambio de Contraseña -->
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2">
                    <i class="bi bi-shield-lock me-2 text-primary"></i>Seguridad y Contraseña
                </h5>

                <?php if ($mensajePass): ?>
                    <div class="alert alert-<?= $tipoPass ?> small d-flex align-items-center mb-3">
                        <i class="bi bi-info-circle-fill fs-5 me-2"></i>
                        <span><?= htmlspecialchars($mensajePass) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="accion" value="cambiar_password">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Contraseña Actual</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" name="password_actual" class="form-control" placeholder="Ingresa tu contraseña actual" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nueva Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password_nueva" class="form-control" placeholder="Mínimo 6 caracteres" minlength="6" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Confirmar Nueva Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                                <input type="password" name="password_confirm" class="form-control" placeholder="Repite la nueva contraseña" minlength="6" required>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-outline-primary px-4 py-2 fw-semibold">
                        <i class="bi bi-arrow-repeat me-1"></i> Actualizar Contraseña
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
