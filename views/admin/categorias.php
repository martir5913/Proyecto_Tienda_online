<?php

declare(strict_types=1);

$tituloPagina = 'Administrar Categorías | Doméstik';
$scriptEspecifico = 'admin_categorias.js';

require_once dirname(__DIR__, 2) . '/app/middlewares/AuthMiddleware.php';
require_once dirname(__DIR__, 2) . '/app/controllers/CategoriaController.php';

use App\Controllers\CategoriaController;
use App\Middlewares\AuthMiddleware;

AuthMiddleware::verificarAdmin();

if (empty($_SESSION['csrf_admin_categorias'])) {
    $_SESSION['csrf_admin_categorias'] = bin2hex(random_bytes(32));
}

$categoriaCtrl = new CategoriaController();

$filtros = [
    'busqueda' => trim((string)($_GET['q'] ?? '')),
    'estado' => $_GET['estado'] ?? null,
];

$categorias = $categoriaCtrl->getCategoriasAdmin($filtros);

require_once dirname(__DIR__) . '/layouts/header.php';
?>

<div
    class="container-fluid py-4 px-lg-5"
    id="admin-categorias"
    data-endpoint="<?= BASE_URL ?>/api/admin_categorias.php"
    data-base-url="<?= BASE_URL ?>"
>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-tags me-2 text-primary"></i>Administrar categorías
            </h3>
            <p class="text-muted small mb-0">
                Crea, consulta, edita y controla las categorías utilizadas por el catálogo.
            </p>
        </div>

        <div class="d-flex gap-2">
            <a
                href="<?= BASE_URL ?>/index.php?ruta=admin_dashboard"
                class="btn btn-outline-secondary"
            >
                <i class="bi bi-arrow-left me-1"></i>Volver al panel
            </a>

            <button
                type="button"
                class="btn btn-primary-app"
                id="btn-nueva-categoria"
            >
                <i class="bi bi-plus-lg me-1"></i>Nueva categoría
            </button>
        </div>
    </div>

    <?php if (isset($_GET['resultado'])): ?>
        <?php
        $mensajes = [
            'creada' => 'La categoría fue registrada correctamente.',
            'actualizada' => 'La categoría fue actualizada correctamente.',
            'eliminada' => 'La categoría fue eliminada correctamente.',
            'activada' => 'La categoría fue activada correctamente.',
            'desactivada' => 'La categoría fue desactivada correctamente.',
        ];
        ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($mensajes[$_GET['resultado']] ?? 'Operación realizada correctamente.') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/index.php" class="row g-3 align-items-end">
                <input type="hidden" name="ruta" value="admin_categorias">

                <div class="col-lg-7">
                    <label for="q" class="form-label fw-semibold small">Buscar categoría</label>
                    <input
                        type="search"
                        class="form-control"
                        id="q"
                        name="q"
                        value="<?= htmlspecialchars($filtros['busqueda'], ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Nombre o descripción"
                    >
                </div>

                <div class="col-md-5 col-lg-3">
                    <label for="estado" class="form-label fw-semibold small">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="">Todos</option>
                        <option value="1" <?= (string)$filtros['estado'] === '1' ? 'selected' : '' ?>>Activas</option>
                        <option value="0" <?= (string)$filtros['estado'] === '0' ? 'selected' : '' ?>>Inactivas</option>
                    </select>
                </div>

                <div class="col-md-7 col-lg-2 d-flex gap-2">
                    <button class="btn btn-outline-primary flex-grow-1" type="submit">
                        <i class="bi bi-search me-1"></i>Filtrar
                    </button>
                    <a
                        class="btn btn-outline-secondary"
                        href="<?= BASE_URL ?>/index.php?ruta=admin_categorias"
                        title="Limpiar filtros"
                    >
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-1">Categorías del catálogo</h5>
                <span class="text-muted small">
                    <?= count($categorias) ?> <?= count($categorias) === 1 ? 'registro' : 'registros' ?> encontrados
                </span>
            </div>
        </div>

        <div class="card-body p-0 pt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Categoría</th>
                            <th>Descripción</th>
                            <th class="text-center">Productos</th>
                            <th class="text-center">Stock</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                                  <?php if (empty($categorias)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    No se encontraron categorías con los filtros seleccionados.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($categorias as $categoria): ?>
                            <?php
                            $activa = (int)$categoria['activo'] === 1;
                            $totalProductos = (int)$categoria['total_productos'];
                            ?>
                            <tr>
                                <td class="ps-4" style="min-width: 220px;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div
                                            class="border rounded-3 bg-light d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width: 48px; height: 48px; overflow: hidden;"
                                        >
                                            <?php if (!empty($categoria['imagen'])): ?>
                                                <img
                                                    src="<?= BASE_URL ?>/public/img/categorias/<?= rawurlencode((string)$categoria['imagen']) ?>"
                                                    alt="<?= htmlspecialchars((string)$categoria['nombre_categoria'], ENT_QUOTES, 'UTF-8') ?>"
                                                    style="width: 100%; height: 100%; object-fit: cover;"
                                                    onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');"
                                                >
                                                <i class="bi bi-tag text-muted d-none"></i>
                                            <?php else: ?>
                                                <i class="bi bi-tag text-muted"></i>
                                            <?php endif; ?>
                                        </div>

                                        <div class="fw-semibold text-dark">
                                            <?= htmlspecialchars((string)$categoria['nombre_categoria'], ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    </div>
                                </td>

                                <td style="min-width: 260px;">
                                    <?php if (!empty($categoria['descripcion'])): ?>
                                        <span class="text-muted small">
                                            <?= htmlspecialchars((string)$categoria['descripcion'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic">Sin descripción</span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center">
                                    <span class="badge text-bg-light border">
                                        <?= $totalProductos ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge text-bg-light border text-dark">
                                        <?= (int)($categoria['stock_total'] ?? 0) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="badge <?= $activa ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                        <?= $activa ? 'Activa' : 'Inactiva' ?>
                                    </span>
                                </td>

                                <td class="text-end pe-4" style="width: 140px; white-space: nowrap;">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary btn-editar-categoria"
                                            data-id="<?= (int)$categoria['id_categoria'] ?>"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="Editar categoría"
                                            aria-label="Editar categoría"
                                        >
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-sm <?= $activa ? 'btn-outline-secondary' : 'btn-outline-success' ?> btn-estado-categoria"
                                            data-id="<?= (int)$categoria['id_categoria'] ?>"
                                            data-activo="<?= $activa ? '0' : '1' ?>"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="<?= $activa ? 'Desactivar categoría' : 'Activar categoría' ?>"
                                            aria-label="<?= $activa ? 'Desactivar categoría' : 'Activar categoría' ?>"
                                        >
                                            <i class="bi <?= $activa ? 'bi-pause-circle' : 'bi-play-circle' ?>"></i>
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger btn-eliminar-categoria"
                                            data-id="<?= (int)$categoria['id_categoria'] ?>"
                                            data-nombre="<?= htmlspecialchars((string)$categoria['nombre_categoria'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-productos="<?= (int)$categoria['total_productos'] ?>"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="Eliminar categoría"
                                            aria-label="Eliminar categoría"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal crear / editar -->
<div class="modal fade" id="modalCategoria" tabindex="-1" aria-labelledby="modalCategoriaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalCategoriaTitulo">Nueva categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="form-categoria" novalidate enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="categoria-alerta" role="alert"></div>

                    <input type="hidden" name="accion" id="categoria-accion" value="crear">
                    <input type="hidden" name="id_categoria" id="categoria-id" value="">
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars((string)$_SESSION['csrf_admin_categorias'], ENT_QUOTES, 'UTF-8') ?>"
                    >

                    <div class="row g-3">
                        <div class="col-md-3 d-none" id="contenedor-id-visual">
                            <label for="categoria-id-visual" class="form-label fw-semibold text-muted">ID Categoría</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-hash"></i></span>
                                <input
                                    type="text"
                                    class="form-control bg-light text-muted fw-bold"
                                    id="categoria-id-visual"
                                    readonly
                                    tabindex="-1"
                                    aria-readonly="true"
                                >
                            </div>
                        </div>

                        <div class="col-md-8" id="contenedor-nombre-col">
                            <label for="categoria-nombre" class="form-label fw-semibold">Nombre de categoría</label>
                            <input
                                type="text"
                                class="form-control"
                                id="categoria-nombre"
                                name="nombre_categoria"
                                maxlength="100"
                                required
                            >
                            <div class="invalid-feedback">Ingrese el nombre de la categoría.</div>
                        </div>

                        <div class="col-md-4" id="contenedor-estado-col">
                            <label for="categoria-activo" class="form-label fw-semibold">Estado</label>
                            <select class="form-select" id="categoria-activo" name="activo" required>
                                <option value="1">Activa</option>
                                <option value="0">Inactiva</option>
                            </select>
                        </div>                  </div>

                        <div class="col-12">
                            <label for="categoria-descripcion" class="form-label fw-semibold">Descripción</label>
                            <textarea
                                class="form-control"
                                id="categoria-descripcion"
                                name="descripcion"
                                rows="4"
                                maxlength="3000"
                                placeholder="Descripción breve de la categoría"
                            ></textarea>
                        </div>

                        <div class="col-12">
                            <label for="categoria-imagen" class="form-label fw-semibold">Imagen de categoría</label>
                            <input
                                type="file"
                                class="form-control"
                                id="categoria-imagen"
                                name="imagen"
                                accept="image/jpeg,image/png,image/webp"
                            >
                            <div class="form-text">Opcional. JPG, PNG o WEBP. Máximo 5 MB.</div>
                        </div>

                        <div class="col-12 d-none" id="categoria-imagen-actual-contenedor">
                            <div class="small fw-semibold mb-2">Imagen actual</div>
                            <img
                                id="categoria-imagen-actual"
                                src=""
                                alt="Imagen actual de la categoría"
                                class="img-thumbnail"
                                style="width: 120px; height: 90px; object-fit: cover;"
                            >
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-app" id="btn-guardar-categoria">
                        <i class="bi bi-check-lg me-1"></i>Guardar categoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal eliminar -->
<div class="modal fade" id="modalEliminarCategoria" tabindex="-1" aria-labelledby="modalEliminarCategoriaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalEliminarCategoriaTitulo">Eliminar categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">¿Desea eliminar la categoría <strong id="eliminar-categoria-nombre"></strong>?</p>
                <p class="text-muted small mb-0">
                    Esta acción solo está disponible cuando la categoría no tiene productos asignados.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-outline-danger" id="btn-confirmar-eliminar-categoria">
                    <i class="bi bi-trash me-1"></i>Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>
