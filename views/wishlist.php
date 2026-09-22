<?php
// Vista: Lista de Deseos (Wishlist)
$tituloPagina = "Lista de Deseos | Doméstik";

require_once dirname(__DIR__) . '/app/middlewares/AuthMiddleware.php';
require_once dirname(__DIR__) . '/app/controllers/WishlistController.php';

use App\Middlewares\AuthMiddleware;
use App\Controllers\WishlistController;

AuthMiddleware::verificarAutenticado();
$idUsuario = (int)$_SESSION['usuario']['id_usuario'];

$wishlistCtrl = new WishlistController();
$favoritos = $wishlistCtrl->getFavoritos($idUsuario);

require_once __DIR__ . '/layouts/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-heart-fill me-2 text-danger"></i>Mi Lista de Deseos</h3>
            <p class="text-muted small mb-0">Artículos y electrodomésticos que has guardado para comprar más adelante.</p>
        </div>
        <a href="<?= BASE_URL ?>/index.php?ruta=catalogo" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-grid me-1"></i> Seguir Explorando
        </a>
    </div>

    <!-- Mensaje cuando la lista queda o está vacía -->
    <div class="text-center py-5 bg-white rounded-4 shadow-sm <?= empty($favoritos) ? '' : 'd-none' ?>" id="wishlist-vacia">
        <i class="bi bi-heartbreak fs-1 text-muted"></i>
        <h5 class="mt-3 text-muted">Tu lista de deseos está vacía</h5>
        <p class="text-muted small">Explora nuestro catálogo y presiona el corazón en cualquier producto para guardarlo aquí.</p>
        <a href="<?= BASE_URL ?>/index.php?ruta=catalogo" class="btn btn-primary-app mt-2">
            <i class="bi bi-grid-fill me-1"></i> Ir al Catálogo
        </a>
    </div>

    <?php if (!empty($favoritos)): ?>
        <div class="row g-4" id="grid-favoritos">
            <?php foreach ($favoritos as $fav): ?>
                <?php
                $imagen = trim((string)($fav['imagen'] ?? ''));
                $idProd = (int)$fav['id_producto'];
                $stock = (int)($fav['stock'] ?? 0);
                $disponible = ($fav['id_estado_producto'] == 1 && $stock > 0);
                ?>
                <div class="col-sm-6 col-lg-4 col-favorito" id="card-fav-<?= $idProd ?>">
                    <div class="product-card h-100 position-relative shadow-sm border-0 rounded-4 overflow-hidden">
                        <!-- Botón flotante para eliminar de favoritos -->
                        <button type="button" 
                                class="btn btn-light rounded-circle shadow-sm position-absolute top-0 end-0 m-3 z-2 text-danger border"
                                title="Eliminar de mi lista de deseos"
                                onclick="ElectroApp.eliminarDeWishlist(<?= $idProd ?>, 'card-fav-<?= $idProd ?>')">
                            <i class="bi bi-trash3"></i>
                        </button>

                        <div class="product-img-wrapper">
                            <?php if ($imagen !== ''): ?>
                                <img src="<?= BASE_URL ?>/public/img/productos/<?= rawurlencode($imagen) ?>"
                                     alt="<?= htmlspecialchars($fav['nombre']) ?>"
                                     loading="lazy"
                                     onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                                <i class="bi bi-box-seam fs-1 text-muted d-none" aria-hidden="true"></i>
                            <?php else: ?>
                                <i class="bi bi-box-seam fs-1 text-muted" aria-hidden="true"></i>
                            <?php endif; ?>
                        </div>

                        <div class="product-card-body d-flex flex-column p-4">
                            <span class="product-brand fw-semibold text-primary small"><?= htmlspecialchars($fav['nombre_marca']) ?></span>
                            <h5 class="product-title fw-bold my-1"><?= htmlspecialchars($fav['nombre']) ?></h5>
                            <p class="text-muted small mb-2"><i class="bi bi-tag me-1"></i><?= htmlspecialchars($fav['nombre_categoria']) ?></p>

                            <div class="small mb-3 <?= $disponible ? 'text-success' : 'text-danger' ?>">
                                <i class="bi <?= $disponible ? 'bi-check-circle' : 'bi-x-circle' ?> me-1"></i>
                                <?= $disponible ? 'Disponible (' . $stock . ' en stock)' : 'Agotado temporalmente' ?>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                                <span class="product-price fw-bold fs-5">Q <?= number_format((float)$fav['precio'], 2) ?></span>
                                <button class="btn btn-sm btn-primary-app px-3" 
                                        onclick="ElectroApp.agregarAlCarrito(<?= $idProd ?>)"
                                        <?= $disponible ? '' : 'disabled' ?>>
                                    <i class="bi bi-cart-plus me-1"></i> Comprar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
