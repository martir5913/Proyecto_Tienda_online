<?php
// Enrutador centralizado para vistas del proyecto.

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Obtener ruta solicitada (por defecto 'home')
$ruta = $_GET['ruta'] ?? 'home';

switch ($ruta) {
    case 'home':
        require_once __DIR__ . '/views/home.php';
        break;

    case 'catalogo':
        require_once __DIR__ . '/views/catalogo.php';
        break;

    case 'carrito':
        require_once __DIR__ . '/views/carrito.php';
        break;

    case 'login':
        require_once __DIR__ . '/views/login.php';
        break;

    case 'registro':
        require_once __DIR__ . '/views/registro.php';
        break;

    case 'mis_pedidos':
        require_once __DIR__ . '/views/mis_pedidos.php';
        break;

    case 'wishlist':
        require_once __DIR__ . '/views/wishlist.php';
        break;

    case 'admin_dashboard':
        require_once __DIR__ . '/views/admin/dashboard.php';
        break;

    case 'logout':
        require_once __DIR__ . '/app/controllers/AuthController.php';
        $auth = new \App\Controllers\AuthController();
        $auth->logout();
        header('Location: ' . BASE_URL . '/index.php');
        exit;

    default:
        http_response_code(404);
        $tituloPagina = "Página no encontrada - 404";
        require_once __DIR__ . '/views/layouts/header.php';
        echo '<div class="container py-5 text-center"><i class="bi bi-exclamation-triangle display-1 text-warning"></i><h2 class="mt-3">Página no encontrada (404)</h2><p class="text-muted">La sección solicitada no existe.</p><a href="' . BASE_URL . '/index.php" class="btn btn-primary-app">Volver al Inicio</a></div>';
        require_once __DIR__ . '/views/layouts/footer.php';
        break;
}
