<?php
// RF16 - Administrar productos
$tituloPagina = 'Administrar Productos | ElectroHogar';
$scriptEspecifico = 'admin_productos.js';

require_once dirname(__DIR__, 2) . '/app/middlewares/AuthMiddleware.php';
require_once dirname(__DIR__, 2) . '/app/controllers/ProductoController.php';

use App\Controllers\ProductoController;
use App\Middlewares\AuthMiddleware;

AuthMiddleware::verificarAdmin();

if (empty($_SESSION['csrf_admin_productos'])) {
    $_SESSION['csrf_admin_productos'] = bin2hex(random_bytes(32));
}

$productoCtrl = new ProductoController();

$filtros = [
    'busqueda' => trim((string)($_GET['q'] ?? '')),
    'categoria' => $_GET['categoria'] ?? null,
    'estado' => $_GET['estado'] ?? null,
];

$productos = $productoCtrl->getProductosAdmin($filtros);
$catalogos = $productoCtrl->getCatalogosAdmin();
$categorias = $catalogos['categorias'];
$marcas = $catalogos['marcas'];
$estados = $catalogos['estados'];

require_once dirname(__DIR__) . '/layouts/header.php';
?>

<div
    class="container-fluid py-4 px-lg-5"
    id="admin-productos"
    data-endpoint="<?= BASE_URL ?>/api/admin_productos.php"
    data-base-url="<?= BASE_URL ?>"
>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-box-seam me-2 text-primary"></i>Administrar productos
            </h3>
            <p class="text-muted small mb-0">
                Registro, consulta, actualización e inventario del catálogo.
            </p>
        </div>

        <button
            type="button"
            class="btn btn-primary-app"
            id="btn-nuevo-producto"
        >
            <i class="bi bi-plus-lg me-1"></i>
            Nuevo producto
        </button>
    </div>

    <?php if (isset($_GET['resultado'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php
            $mensajes = [
                'creado' => 'El producto fue registrado correctamente.',
                'actualizado' => 'El producto fue actualizado correctamente.',
                'eliminado' => 'El producto fue retirado del catálogo.',
            ];
            echo htmlspecialchars($mensajes[$_GET['resultado']] ?? 'Operación realizada correctamente.');
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/index.php" class="row g-3 align-items-end">
                <input type="hidden" name="ruta" value="admin_productos">

                <div class="col-lg-5">
                    <label for="q" class="form-label fw-semibold small">Buscar producto</label>
                    <input
                        type="search"
                        class="form-control"
                        id="q"
                        name="q"
                        value="<?= htmlspecialchars($filtros['busqueda']) ?>"
                        placeholder="Nombre, modelo o marca"
                    >
                </div>

                <div class="col-md-4 col-lg-3">
                    <label for="categoria" class="form-label fw-semibold small">Categoría</label>
                    <select class="form-select" id="categoria" name="categoria">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option
                                value="<?= (int)$categoria['id_categoria'] ?>"
                                <?= (string)$filtros['categoria'] === (string)$categoria['id_categoria'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($categoria['nombre_categoria']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4 col-lg-2">
                    <label for="estado" class="form-label fw-semibold small">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="">Todos</option>
                        <?php foreach ($estados as $estado): ?>
                            <option
                                value="<?= (int)$estado['id_estado_producto'] ?>"
                                <?= (string)$filtros['estado'] === (string)$estado['id_estado_producto'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($estado['nombre_estado']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4 col-lg-2 d-flex gap-2">
                    <button class="btn btn-outline-primary flex-grow-1" type="submit">
                        <i class="bi bi-search me-1"></i>Filtrar
                    </button>
                    <a
                        class="btn btn-outline-secondary"
                        href="<?= BASE_URL ?>/index.php?ruta=admin_productos"
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
                <h5 class="fw-bold mb-1">Inventario de productos</h5>
                <span class="text-muted small">
                    <?= count($productos) ?> <?= count($productos) === 1 ? 'registro' : 'registros' ?> encontrados
                </span>
            </div>
        </div>

        <div class="card-body p-0 pt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Producto</th>
                            <th>Marca / Categoría</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th>Destacado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($productos)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    No se encontraron productos con los filtros seleccionados.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td class="ps-4" style="min-width: 290px;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div
                                            class="border rounded-3 bg-light d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width: 64px; height: 64px; overflow: hidden;"
                                        >
                                            <?php if (!empty($producto['imagen'])): ?>
                                                <img
                                                    src="<?= BASE_URL ?>/public/img/productos/<?= rawurlencode($producto['imagen']) ?>"
                                                    alt="<?= htmlspecialchars($producto['nombre']) ?>"
                                                    style="width: 100%; height: 100%; object-fit: contain;"
                                                    onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');"
                                                >
                                                <i class="bi bi-box-seam text-muted d-none"></i>
                                            <?php else: ?>
                                                <i class="bi bi-box-seam text-muted"></i>
                                            <?php endif; ?>
                                        </div>

                                        <div>
                                            <div class="fw-semibold text-dark">
                                                <?= htmlspecialchars($producto['nombre']) ?>
                                            </div>
                                            <div class="small text-muted">
                                                Modelo: <?= htmlspecialchars($producto['codigo_modelo']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($producto['nombre_marca']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($producto['nombre_categoria']) ?></div>
                                </td>
                                <td class="fw-semibold">Q <?= number_format((float)$producto['precio'], 2) ?></td>
                                <td>
                                    <span class="<?= (int)$producto['stock'] > 0 ? 'text-success' : 'text-danger' ?> fw-semibold">
                                        <?= (int)$producto['stock'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $claseEstado = match ((int)$producto['id_estado_producto']) {
                                        1 => 'text-bg-success',
                                        2 => 'text-bg-warning',
                                        default => 'text-bg-secondary',
                                    };
                                    ?>
                                    <span class="badge <?= $claseEstado ?>">
                                        <?= htmlspecialchars($producto['estado_nombre']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ((int)$producto['destacado'] === 1): ?>
                                        <span class="badge bg-primary-subtle text-primary">Sí</span>
                                    <?php else: ?>
                                        <span class="text-muted small">No</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 text-nowrap">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary btn-editar-producto"
                                        data-id="<?= (int)$producto['id_producto'] ?>"
                                        title="Editar producto"
                                    >
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger btn-eliminar-producto ms-1"
                                        data-id="<?= (int)$producto['id_producto'] ?>"
                                        data-nombre="<?= htmlspecialchars($producto['nombre'], ENT_QUOTES) ?>"
                                        <?= (int)$producto['id_estado_producto'] === 3 ? 'disabled' : '' ?>
                                        title="Eliminar del catálogo"
                                    >
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

    <div class="mt-3">
        <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/index.php?ruta=admin_dashboard">
            <i class="bi bi-arrow-left me-1"></i>Volver al panel
        </a>
    </div>
</div>

<!-- Modal Crear / Editar -->
<div class="modal fade" id="modalProducto" tabindex="-1" aria-labelledby="modalProductoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form
            id="form-producto"
            class="modal-content border-0 shadow"
            enctype="multipart/form-data"
            novalidate>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalProductoTitulo">Nuevo producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div id="producto-alerta" class="alert d-none" role="alert"></div>

                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_admin_productos']) ?>">
                    <input type="hidden" name="accion" id="producto-accion" value="crear">
                    <input type="hidden" name="id_producto" id="producto-id" value="">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="producto-nombre" class="form-label fw-semibold">Nombre</label>
                            <input type="text" class="form-control" id="producto-nombre" name="nombre" maxlength="200" required>
                        </div>

                        <div class="col-md-4">
                            <label for="producto-modelo" class="form-label fw-semibold">Código / modelo</label>
                            <input type="text" class="form-control" id="producto-modelo" name="codigo_modelo" maxlength="50" required>
                        </div>

                        <div class="col-md-6">
                            <label for="producto-categoria" class="form-label fw-semibold">Categoría</label>
                            <select class="form-select" id="producto-categoria" name="id_categoria" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= (int)$categoria['id_categoria'] ?>">
                                        <?= htmlspecialchars($categoria['nombre_categoria']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="producto-marca" class="form-label fw-semibold">Marca</label>
                            <select class="form-select" id="producto-marca" name="id_marca" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($marcas as $marca): ?>
                                    <option value="<?= (int)$marca['id_marca'] ?>">
                                        <?= htmlspecialchars($marca['nombre_marca']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="producto-precio" class="form-label fw-semibold">Precio (Q)</label>
                            <input type="number" class="form-control" id="producto-precio" name="precio" min="0" step="0.01" required>
                        </div>

                        <div class="col-md-4">
                            <label for="producto-stock" class="form-label fw-semibold">Stock</label>
                            <input type="number" class="form-control" id="producto-stock" name="stock" min="0" step="1" required>
                        </div>

                        <div class="col-md-4">
                            <label for="producto-estado" class="form-label fw-semibold">Estado</label>
                            <select class="form-select" id="producto-estado" name="id_estado_producto" required>
                                <?php foreach ($estados as $estado): ?>
                                    <option
                                        value="<?= (int)$estado['id_estado_producto'] ?>"
                                        <?= (int)$estado['id_estado_producto'] === 1 ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($estado['nombre_estado']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="producto-descripcion" class="form-label fw-semibold">Descripción</label>
                            <textarea class="form-control" id="producto-descripcion" name="descripcion" rows="3" maxlength="5000"></textarea>
                        </div>

                        <div class="col-12">
                            <label for="producto-especificaciones" class="form-label fw-semibold">Especificaciones</label>
                            <textarea
                                class="form-control"
                                id="producto-especificaciones"
                                name="especificaciones"
                                rows="3"
                                maxlength="5000"
                                placeholder="Ejemplo: Capacidad: 20 Kg | 12 ciclos | Acero inoxidable"
                            ></textarea>
                        </div>

                        <div class="col-md-8">
                            <label for="producto-imagen" class="form-label fw-semibold">Imagen</label>
                            <input
                                type="file"
                                class="form-control"
                                id="producto-imagen"
                                name="imagen"
                                accept="image/jpeg,image/png,image/webp"
                            >
                            <div class="form-text">JPG, PNG o WEBP. Máximo 5 MB.</div>
                        </div>

                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="producto-destacado" name="destacado">
                                <label class="form-check-label fw-semibold" for="producto-destacado">
                                    Mostrar como destacado
                                </label>
                            </div>
                        </div>

                        <div class="col-12 d-none" id="producto-imagen-actual-contenedor">
                            <span class="small fw-semibold d-block mb-2">Imagen actual</span>
                            <div class="border rounded-3 bg-light p-2 d-inline-flex align-items-center justify-content-center">
                                <img
                                    id="producto-imagen-actual"
                                    src=""
                                    alt="Imagen actual"
                                    style="max-width: 160px; max-height: 120px; object-fit: contain;"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-app" id="btn-guardar-producto">
                        <i class="bi bi-floppy me-1"></i>Guardar producto
                    </button>
                </div>
        </form>
    </div>
</div>

<!-- Modal de eliminación lógica -->
<div class="modal fade" id="modalEliminarProducto" tabindex="-1" aria-labelledby="modalEliminarProductoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalEliminarProductoTitulo">Eliminar producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">
                    ¿Desea retirar del catálogo el producto <strong id="eliminar-producto-nombre"></strong>?
                </p>
                <p class="small text-muted mb-0">
                    El registro se conservará como Descontinuado para no afectar pedidos o información histórica.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btn-confirmar-eliminar-producto">
                    <i class="bi bi-trash3 me-1"></i>Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>