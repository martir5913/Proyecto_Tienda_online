<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/middlewares/AuthMiddleware.php';
require_once dirname(__DIR__) . '/app/controllers/CategoriaController.php';

use App\Controllers\CategoriaController;
use App\Middlewares\AuthMiddleware;

AuthMiddleware::verificarAdmin();

$categoriaCtrl = new CategoriaController();

function validarCsrfAdminCategorias(): void
{
    $tokenSesion = (string)($_SESSION['csrf_admin_categorias'] ?? '');
    $tokenRecibido = (string)($_POST['csrf_token'] ?? '');

    if (
        $tokenSesion === '' ||
        $tokenRecibido === '' ||
        !hash_equals($tokenSesion, $tokenRecibido)
    ) {
        jsonResponse(
            false,
            'La sesión del formulario expiró. Recargue la página e intente nuevamente.',
            null,
            419
        );
    }
}

function guardarImagenCategoria(array $archivo): ?string
{
    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($archivo['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No fue posible cargar la imagen seleccionada.');
    }

    $tamanoMaximo = 5 * 1024 * 1024;
    $tamano = (int)($archivo['size'] ?? 0);

    if ($tamano <= 0 || $tamano > $tamanoMaximo) {
        throw new RuntimeException('La imagen debe pesar como máximo 5 MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file((string)$archivo['tmp_name']);

    $permitidos = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($permitidos[$mime])) {
        throw new RuntimeException('Formato de imagen no permitido. Use JPG, PNG o WEBP.');
    }

    $directorio = PUBLIC_DIR . '/img/categorias';
    if (!is_dir($directorio) && !mkdir($directorio, 0775, true) && !is_dir($directorio)) {
        throw new RuntimeException('No fue posible preparar la carpeta de imágenes de categorías.');
    }

    $nombre = 'cat_' . date('Ymd_His') . '_' . bin2hex(random_bytes(5)) . '.' . $permitidos[$mime];
    $destino = $directorio . '/' . $nombre;

    if (!move_uploaded_file((string)$archivo['tmp_name'], $destino)) {
        throw new RuntimeException('No fue posible guardar la imagen de la categoría.');
    }

    return $nombre;
}

function eliminarImagenCategoriaTemporal(?string $imagen): void
{
    if ($imagen === null || $imagen === '') {
        return;
    }

    $ruta = PUBLIC_DIR . '/img/categorias/' . basename($imagen);
    if (is_file($ruta)) {
        @unlink($ruta);
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $accion = (string)($_GET['accion'] ?? '');

        if ($accion !== 'detalle') {
            jsonResponse(false, 'Acción no permitida.', null, 405);
        }

        $idCategoria = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$idCategoria || $idCategoria <= 0) {
            jsonResponse(false, 'Categoría inválida.', null, 422);
        }

        $categoria = $categoriaCtrl->getDetalleAdmin((int)$idCategoria);
        if (!$categoria) {
            jsonResponse(false, 'Categoría no encontrada.', null, 404);
        }

        jsonResponse(true, 'Categoría obtenida correctamente.', $categoria);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, 'Método no permitido.', null, 405);
    }

    validarCsrfAdminCategorias();

    $accion = (string)($_POST['accion'] ?? '');

    if ($accion === 'crear') {
        $imagenNueva = guardarImagenCategoria($_FILES['imagen'] ?? []);

        try {
            $idCategoria = $categoriaCtrl->crearCategoriaAdmin($_POST, $imagenNueva);
        } catch (Throwable $e) {
            eliminarImagenCategoriaTemporal($imagenNueva);
            throw $e;
        }

        jsonResponse(
            true,
            'Categoría registrada correctamente.',
            ['id_categoria' => $idCategoria],
            201
        );
    }

    if ($accion === 'actualizar') {
        $idCategoria = filter_var($_POST['id_categoria'] ?? null, FILTER_VALIDATE_INT);
        if ($idCategoria === false || $idCategoria <= 0) {
            throw new InvalidArgumentException('Categoría inválida.');
        }

        $imagenNueva = guardarImagenCategoria($_FILES['imagen'] ?? []);

        try {
            $categoriaCtrl->actualizarCategoriaAdmin(
                (int)$idCategoria,
                $_POST,
                $imagenNueva
            );
        } catch (Throwable $e) {
            eliminarImagenCategoriaTemporal($imagenNueva);
            throw $e;
        }

        jsonResponse(true, 'Categoría actualizada correctamente.');
    }

    if ($accion === 'estado') {
        $idCategoria = filter_var($_POST['id_categoria'] ?? null, FILTER_VALIDATE_INT);
        $activo = filter_var($_POST['activo'] ?? null, FILTER_VALIDATE_INT);

        if ($idCategoria === false || $idCategoria <= 0) {
            throw new InvalidArgumentException('Categoría inválida.');
        }

        if ($activo === false || !in_array((int)$activo, [0, 1], true)) {
            throw new InvalidArgumentException('Estado inválido.');
        }

        $categoriaCtrl->cambiarEstadoAdmin((int)$idCategoria, (int)$activo);

        jsonResponse(
            true,
            (int)$activo === 1
                ? 'Categoría activada correctamente.'
                : 'Categoría desactivada correctamente.'
        );
    }

    if ($accion === 'eliminar') {
        $idCategoria = filter_var($_POST['id_categoria'] ?? null, FILTER_VALIDATE_INT);
        if ($idCategoria === false || $idCategoria <= 0) {
            throw new InvalidArgumentException('Categoría inválida.');
        }

        $categoriaCtrl->eliminarCategoriaAdmin((int)$idCategoria);
        jsonResponse(true, 'Categoría eliminada correctamente.');
    }

    jsonResponse(false, 'Acción no permitida.', null, 422);
} catch (InvalidArgumentException $e) {
    jsonResponse(false, $e->getMessage(), null, 422);
} catch (RuntimeException $e) {
    jsonResponse(false, $e->getMessage(), null, 404);
} catch (Throwable $e) {
    error_log('RF17 categorías: ' . $e->getMessage());
    jsonResponse(false, 'Ocurrió un error al procesar la categoría.', null, 500);
}
