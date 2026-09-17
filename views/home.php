<?php
// Vista: Página Principal (Home)
$tituloPagina = "ElectroHogar | Los Mejores Electrodomésticos y Línea Blanca";

require_once dirname(__DIR__) . '/app/controllers/ProductoController.php';
use App\Controllers\ProductoController;

$productoCtrl = new ProductoController();
$destacados = $productoCtrl->getDestacados();

require_once __DIR__ . '/layouts/header.php';
?>

<!-- Banner Hero Principal -->
<section class="py-5 bg-dark text-white text-center text-lg-start position-relative overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);">
    <div class="container py-4">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-3">
                    <i class="bi bi-tag-fill me-1"></i> Temporada de Innovación 2026
                </span>
                <h1 class="display-4 fw-bold text-white mb-3">Tecnología y Confort para tu Hogar</h1>
                <p class="lead text-slate-300 text-light mb-4 opacity-75">Descubre nuestra línea completa de refrigeradoras, estufas, lavadoras y pequeños electrodomésticos con máxima eficiencia energética.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= BASE_URL ?>/index.php?ruta=catalogo" class="btn btn-primary-app btn-lg">
                        <i class="bi bi-grid-fill me-2"></i> Explorar Catálogo
                    </a>
                    <a href="<?= BASE_URL ?>/index.php?ruta=catalogo&categoria=1" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-snow me-2"></i> Ver Refrigeración
                    </a>
                </div>
            </div>
            <div class="col-lg-5 text-center mt-4 mt-lg-0">
                <div class="p-4 rounded-4 bg-white bg-opacity-10 border border-light border-opacity-25 shadow-lg">
                    <i class="bi bi-shield-check display-3 text-warning mb-2"></i>
                    <h4 class="text-white fw-bold">Garantía Certificada</h4>
                    <p class="text-light small opacity-75 mb-0">Hasta 10 años de garantía en compresores y motores Inverter Direct Drive de primeras marcas.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Beneficios Rápidos -->
<section class="py-4 border-bottom bg-white">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="d-flex align-items-center justify-content-center">
                    <i class="bi bi-truck fs-2 text-primary me-3"></i>
                    <div class="text-start">
                        <h6 class="mb-0 fw-bold">Envío Seguro</h6>
                        <small class="text-muted">Transporte especializado para línea blanca</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center justify-content-center">
                    <i class="bi bi-credit-card-2-front fs-2 text-primary me-3"></i>
                    <div class="text-start">
                        <h6 class="mb-0 fw-bold">Múltiples Formas de Pago</h6>
                        <small class="text-muted">Tarjetas, transferencia o contra entrega</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center justify-content-center">
                    <i class="bi bi-headset fs-2 text-primary me-3"></i>
                    <div class="text-start">
                        <h6 class="mb-0 fw-bold">Soporte y Garantía</h6>
                        <small class="text-muted">Asistencia técnica y respaldo directo</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Productos Destacados -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Electrodomésticos Destacados</h2>
                <p class="text-muted small mb-0">Selección de los equipos más solicitados por nuestros clientes</p>
            </div>
            <a href="<?= BASE_URL ?>/index.php?ruta=catalogo" class="btn btn-outline-primary btn-sm fw-semibold">
                Ver todo el catálogo <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($destacados as $prod): ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="product-card">
                        <div class="product-img-wrapper">
                            <i class="bi bi-box-seam fs-1 text-muted"></i>
                        </div>
                        <div class="product-card-body">
                            <span class="product-brand"><?= htmlspecialchars($prod['nombre_marca']) ?></span>
                            <h3 class="product-title"><?= htmlspecialchars($prod['nombre']) ?></h3>
                            <div class="text-muted small mb-3">Modelo: <?= htmlspecialchars($prod['codigo_modelo']) ?></div>
                            <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                                <span class="product-price">Q <?= number_format($prod['precio'], 2) ?></span>
                                <button class="btn btn-sm btn-primary-app" onclick="ElectroApp.agregarAlCarrito(<?= $prod['id_producto'] ?>)">
                                    <i class="bi bi-cart-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
