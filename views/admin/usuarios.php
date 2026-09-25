<?php

declare(strict_types=1);

$tituloPagina = 'Gestión de Usuarios y Roles | Doméstik';
$scriptEspecifico = 'admin_usuarios.js';

require_once dirname(__DIR__, 2) . '/app/middlewares/AuthMiddleware.php';

use App\Middlewares\AuthMiddleware;

AuthMiddleware::verificarAdmin();

if (empty($_SESSION['csrf_admin_usuarios'])) {
    $_SESSION['csrf_admin_usuarios'] = bin2hex(random_bytes(32));
}

$csrfToken = (string)$_SESSION['csrf_admin_usuarios'];
$idSesionActual = (int)($_SESSION['usuario']['id_usuario'] ?? 0);

require_once dirname(__DIR__) . '/layouts/header.php';
?>

<div
    class="container-fluid py-4 px-lg-5"
    id="admin-usuarios-modulo"
    data-api-url="<?= BASE_URL ?>/api/admin_usuarios.php"
    data-csrf-token="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"
    data-sesion-id="<?= $idSesionActual ?>"
>
    <!-- Encabezado de Página -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-people-fill me-2 text-primary"></i>Gestión de Usuarios y Roles
            </h3>
            <p class="text-muted small mb-0">
                Administración de cuentas de clientes, privilegios de acceso y control de estados.
            </p>
        </div>
        <a href="<?= BASE_URL ?>/index.php?ruta=admin_dashboard" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Volver al panel
        </a>
    </div>

    <!-- Tarjetas de Métricas Rápidas -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
                <div class="d-flex align-items-center">
                    <div class="p-2 bg-primary-subtle text-primary rounded-3 me-3">
                        <i class="bi bi-people fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold">Total Cuentas</span>
                        <h4 class="fw-bold mb-0" id="kpi-total-usuarios">0</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
                <div class="d-flex align-items-center">
                    <div class="p-2 bg-info-subtle text-info rounded-3 me-3">
                        <i class="bi bi-person-check fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold">Clientes</span>
                        <h4 class="fw-bold mb-0" id="kpi-total-clientes">0</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
                <div class="d-flex align-items-center">
                    <div class="p-2 bg-purple-subtle text-dark rounded-3 me-3" style="background-color: #f1f5f9;">
                        <i class="bi bi-shield-lock-fill fs-4 text-primary"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold">Administradores</span>
                        <h4 class="fw-bold mb-0" id="kpi-total-admins">0</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
                <div class="d-flex align-items-center">
                    <div class="p-2 bg-success-subtle text-success rounded-3 me-3">
                        <i class="bi bi-check-circle fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold">Activos</span>
                        <h4 class="fw-bold mb-0" id="kpi-total-activos">0</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
                <div class="d-flex align-items-center">
                    <div class="p-2 bg-danger-subtle text-danger rounded-3 me-3">
                        <i class="bi bi-slash-circle fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold">Bloqueados / Inactivos</span>
                        <h4 class="fw-bold mb-0" id="kpi-total-bloqueados">0</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form id="form-filtros-usuarios" class="row g-2 align-items-center">
                <div class="col-md-4 col-lg-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input
                            type="search"
                            id="filtro-busqueda"
                            class="form-control border-start-0"
                            placeholder="Buscar por nombre, apellido, correo o teléfono..."
                        >
                    </div>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <select id="filtro-rol" class="form-select form-select-sm">
                        <option value="">Todos los roles</option>
                    </select>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <select id="filtro-estado" class="form-select form-select-sm">
                        <option value="">Todos los estados</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 col-lg-3 d-flex gap-2 justify-content-md-end">
                    <select id="filtro-orden" class="form-select form-select-sm">
                        <option value="recientes">Más recientes</option>
                        <option value="antiguos">Más antiguos</option>
                        <option value="nombre_asc">Nombre (A - Z)</option>
                        <option value="nombre_desc">Nombre (Z - A)</option>
                    </select>
                    <button type="button" id="btn-limpiar-filtros" class="btn btn-outline-secondary btn-sm" title="Restablecer filtros">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0" id="tabla-usuarios">
                <thead class="table-light">
                    <tr>
                        <th>Usuario</th>
                        <th>Contacto</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Pedidos</th>
                        <th>Registro</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody id="lista-usuarios">
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                            Cargando usuarios...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Editar / Gestionar Usuario -->
<div class="modal fade" id="modal-usuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modal-usuario-titulo">
                    <i class="bi bi-person-gear me-2 text-primary"></i>Gestionar Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="form-editar-usuario">
                <div class="modal-body pt-3">
                    <input type="hidden" id="edit-id-usuario" name="id_usuario">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nombre</label>
                            <input type="text" id="edit-nombre" name="nombre" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Apellido</label>
                            <input type="text" id="edit-apellido" name="apellido" class="form-control form-control-sm" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Correo Electrónico</label>
                            <input type="email" id="edit-correo" name="correo" class="form-control form-control-sm bg-light" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Teléfono</label>
                            <input type="tel" id="edit-telefono" name="telefono" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Dirección de Entrega</label>
                        <textarea id="edit-direccion" name="direccion" class="form-control form-control-sm" rows="2"></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Rol de Usuario</label>
                            <select id="edit-rol" name="id_rol" class="form-select form-select-sm" required>
                                <!-- Opciones cargadas dinámicamente -->
                            </select>
                            <small class="text-muted extra-small" id="aviso-propio-rol"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Estado de Cuenta</label>
                            <select id="edit-estado" name="id_estado_usuario" class="form-select form-select-sm" required>
                                <!-- Opciones cargadas dinámicamente -->
                            </select>
                            <small class="text-muted extra-small" id="aviso-propio-estado"></small>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-3">
                        <label class="form-label small fw-semibold mb-1">
                            <i class="bi bi-key me-1"></i> Restablecer Contraseña (Opcional)
                        </label>
                        <p class="text-muted small mb-2">Deja este campo vacío si no deseas modificar la contraseña actual.</p>
                        <input type="password" id="edit-password" name="password" class="form-control form-control-sm" placeholder="Nueva contraseña (mínimo 6 caracteres)">
                    </div>

                    <!-- Resumen de actividad del usuario -->
                    <div class="d-flex justify-content-between align-items-center p-3 border rounded-3 bg-white">
                        <div>
                            <span class="text-muted small">Total de Pedidos Realizados:</span>
                            <span class="fw-bold ms-1" id="info-pedidos-count">0</span>
                        </div>
                        <div>
                            <span class="text-muted small">Monto Total Comprado:</span>
                            <span class="fw-bold text-primary ms-1" id="info-pedidos-monto">Q 0.00</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-app btn-sm px-3" id="btn-guardar-usuario">
                        <i class="bi bi-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>
