<?php
// Vista: Lista de Deseos (Wishlist)
$tituloPagina = "Lista de Deseos | ElectroHogar";

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
    <h3 class="fw-bold mb-4"><i class="bi bi-heart me-2 text-danger"></i>Mi Lista de Deseos</h3>

    <?php if (empty($favoritos)): ?>
        <div class="text-center py-5 bg-white rounded-3 shadow-sm">
            <i class="bi bi-heartbreak fs-1 text-muted"></i>
            <h5 class="mt-3 text-muted">No tienes electrodomésticos guardados</h5>
            <p class="text-muted small">Guarda los productos que te interesen para comprarlos más tarde.</p>
            <a href="<?= BASE_URL ?>/index.php?ruta=catalogo" class="btn btn-primary-app">Explorar Catálogo</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($favoritos as $fav): ?>
                <div class="col-sm-6 col-lg-4">
                    <div class="product-card">
                        <div class="product-img-wrapper">
                            <i class="bi bi-box-seam fs-1 text-muted"></i>
                        </div>
                        <div class="product-card-body">
                            <span class="product-brand"><?= htmlspecialchars($fav['nombre_marca']) ?></span>
                            <h4 class="product-title"><?= htmlspecialchars($fav['nombre']) ?></h4>
                            <p class="text-muted small">Categoría: <?= htmlspecialchars($fav['nombre_categoria']) ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                                <span class="product-price">Q <?= number_format($fav['precio'], 2) ?></span>
                                <button class="btn btn-sm btn-primary-app" onclick="ElectroApp.agregarAlCarrito(<?= $fav['id_producto'] ?>)">
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
