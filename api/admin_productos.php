<?php
// RF16 - API administrativa de productos.
// Solo accesible para usuarios con rol Administrador.

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/middlewares/AuthMiddleware.php';
require_once dirname(__DIR__) . '/app/controllers/ProductoController.php';

use App\Controllers\ProductoController;
use App\Middlewares\AuthMiddleware;

AuthMiddleware::verificarAdmin();

$productoCtrl = new ProductoController();

function validarCsrfAdminProductos(): void
{
    $tokenSesion = $_SESSION['csrf_admin_productos'] ?? '';
    $tokenRecibido = (string)($_POST['csrf_token'] ?? '');

    if ($tokenSesion === '' || $tokenRecibido === '' || !hash_equals($tokenSesion, $tokenRecibido)) {
        jsonResponse(false, 'La sesión del formulario expiró. Recargue la página e intente nuevamente.', null, 419);
    }
}

function guardarImagenProducto(array $archivo): ?string
{
    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($archivo['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No fue posible cargar la imagen seleccionada.');
    }

    $tamanoMaximo = 5 * 1024 * 1024;
    if (($archivo['size'] ?? 0) <= 0 || $archivo['size'] > $tamanoMaximo) {
        throw new RuntimeException('La imagen debe pesar como máximo 5 MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($archivo['tmp_name']);

    $permitidos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($permitidos[$mime])) {
        throw new RuntimeException('Formato de imagen no permitido. Use JPG, PNG o WEBP.');
    }

    $directorio = PUBLIC_DIR . '/img/productos';
    if (!is_dir($directorio) && !mkdir($directorio, 0775, true) && !is_dir($directorio)) {
        throw new RuntimeException('No fue posible preparar la carpeta de imágenes.');
    }

    $nombre = 'prod_' . date('Ymd_His') . '_' . bin2hex(random_bytes(5)) . '.' . $permitidos[$mime];
    $destino = $directorio . '/' . $nombre;

    if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
        throw new RuntimeException('No fue posible guardar la imagen del producto.');
    }

    return $nombre;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $accion = $_GET['accion'] ?? '';

        if ($accion !== 'detalle') {
            jsonResponse(false, 'Acción no permitida.', null, 405);
        }

        $idProducto = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$idProducto || $idProducto <= 0) {
            jsonResponse(false, 'Producto inválido.', null, 422);
        }

        $producto = $productoCtrl->getDetalle((int)$idProducto);
        if (!$producto) {
            jsonResponse(false, 'Producto no encontrado.', null, 404);
        }

        jsonResponse(true, 'Producto obtenido.', $producto);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, 'Método no permitido.', null, 405);
    }

    validarCsrfAdminProductos();

    $accion = (string)($_POST['accion'] ?? '');

    if ($accion === 'crear') {
        $imagenNueva = guardarImagenProducto($_FILES['imagen'] ?? []);

        try {
            $id = $productoCtrl->crearProductoAdmin($_POST, $imagenNueva);
        } catch (Throwable $e) {
            if ($imagenNueva) {
                $ruta = PUBLIC_DIR . '/img/productos/' . $imagenNueva;
                if (is_file($ruta)) {
                    @unlink($ruta);
                }
            }
            throw $e;
        }

        jsonResponse(true, 'Producto registrado correctamente.', ['id_producto' => $id], 201);
    }

    if ($accion === 'actualizar') {
        $idProducto = filter_var($_POST['id_producto'] ?? null, FILTER_VALIDATE_INT);
        if ($idProducto === false || $idProducto <= 0) {
            throw new InvalidArgumentException('Producto inválido.');
        }

        $imagenNueva = guardarImagenProducto($_FILES['imagen'] ?? []);

        try {
            $productoCtrl->actualizarProductoAdmin((int)$idProducto, $_POST, $imagenNueva);
        } catch (Throwable $e) {
            if ($imagenNueva) {
                $ruta = PUBLIC_DIR . '/img/productos/' . $imagenNueva;
                if (is_file($ruta)) {
                    @unlink($ruta);
                }
            }
            throw $e;
        }

        jsonResponse(true, 'Producto actualizado correctamente.');
    }

    if ($accion === 'eliminar') {
        $idProducto = filter_var($_POST['id_producto'] ?? null, FILTER_VALIDATE_INT);
        if ($idProducto === false || $idProducto <= 0) {
            throw new InvalidArgumentException('Producto inválido.');
        }

        $productoCtrl->eliminarProductoAdmin((int)$idProducto);
        jsonResponse(true, 'Producto eliminado del catálogo. Se conservó como descontinuado.');
    }

    jsonResponse(false, 'Acción no permitida.', null, 422);
} catch (InvalidArgumentException $e) {
    jsonResponse(false, $e->getMessage(), null, 422);
} catch (RuntimeException $e) {
    jsonResponse(false, $e->getMessage(), null, 404);
} catch (Throwable $e) {
    error_log('RF16 productos: ' . $e->getMessage());
    jsonResponse(false, 'Ocurrió un error al procesar la operación de productos.', null, 500);
}
