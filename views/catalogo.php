<?php
// Vista: Catálogo con Barra Lateral de Filtros Asíncronos
$tituloPagina = "Catálogo de Electrodomésticos y Línea Blanca | ElectroHogar";
$scriptEspecifico = "catalogo.js";

require_once dirname(__DIR__) . '/app/controllers/ProductoController.php';
require_once dirname(__DIR__) . '/app/controllers/CategoriaController.php';
require_once dirname(__DIR__) . '/app/models/Marca.php';

use App\Controllers\ProductoController;
use App\Controllers\CategoriaController;
use App\Models\Marca;

$productoCtrl = new ProductoController();
$categoriaCtrl = new CategoriaController();
$marcaModel = new Marca();

$categorias = $categoriaCtrl->getTodas();
$marcas = $marcaModel->obtenerTodas();

$filtros = [
    'categoria' => $_GET['categoria'] ?? '',
    'marca'     => $_GET['marca'] ?? '',
    'busqueda'  => $_GET['q'] ?? ''
];
$productos = $productoCtrl->getCatalogo($filtros);

require_once __DIR__ . '/layouts/header.php';
?>

<div class="container py-4">
    <!-- Migas de Pan -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Catálogo de Productos</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Barra Lateral de Filtros -->
        <aside class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-funnel me-1"></i> Filtros</h5>
                    <a href="<?= BASE_URL ?>/index.php?ruta=catalogo" class="small text-decoration-none text-muted">Limpiar</a>
                </div>

                <form id="form-filtros">
                    <!-- Búsqueda rápida -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Búsqueda rápida</label>
                        <input type="text" id="input-busqueda-catalogo" name="q" class="form-control form-control-sm" 
                               value="<?= htmlspecialchars($filtros['busqueda']) ?>" placeholder="Nombre o modelo...">
                    </div>

                    <!-- Categorías -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Categoría</label>
                        <select name="categoria" class="form-select form-select-sm">
                            <option value="">Todas las Categorías</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id_categoria'] ?>" <?= $filtros['categoria'] == $cat['id_categoria'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nombre_categoria']) ?> (<?= $cat['total_productos'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Marcas -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Marca</label>
                        <select name="marca" class="form-select form-select-sm">
                            <option value="">Todas las Marcas</option>
                            <?php foreach ($marcas as $m): ?>
                                <option value="<?= $m['id_marca'] ?>" <?= $filtros['marca'] == $m['id_marca'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['nombre_marca']) ?> (<?= $m['total_productos'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Rango de Precio -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Precio Máximo (Q)</label>
                        <input type="number" name="max" class="form-control form-control-sm" placeholder="Ej. 10000" min="0">
                    </div>
                </form>
            </div>
        </aside>

        <!-- Cuadrícula de Productos -->
        <section class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0">Catálogo de Productos</h4>
                <span class="text-muted small">Mostrando <?= count($productos) ?> artículos</span>
            </div>

            <div class="row g-3" id="grid-productos">
                <?php if (empty($productos)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="mt-2 text-muted">No se encontraron productos con los criterios seleccionados.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($productos as $p): ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="product-card">
                                <div class="product-img-wrapper">
                                    <i class="bi bi-box-seam fs-1 text-muted"></i>
                                </div>
                                <div class="product-card-body">
                                    <span class="product-brand"><?= htmlspecialchars($p['nombre_marca']) ?></span>
                                    <h3 class="product-title"><?= htmlspecialchars($p['nombre']) ?></h3>
                                    <p class="text-muted small mb-2"><?= htmlspecialchars(substr($p['especificaciones'] ?? '', 0, 65)) ?>...</p>
                                    <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                                        <span class="product-price">Q <?= number_format($p['precio'], 2) ?></span>
                                        <button class="btn btn-sm btn-primary-app" onclick="ElectroApp.agregarAlCarrito(<?= $p['id_producto'] ?>)">
                                            <i class="bi bi-cart-plus me-1"></i> Añadir
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
