<?php
// Controlador de Productos
// Maneja catálogo público y CRUD administrativo de productos.

namespace App\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use InvalidArgumentException;
use RuntimeException;

class ProductoController
{
    private Producto $productoModel;
    private Categoria $categoriaModel;
    private Marca $marcaModel;

    public function __construct()
    {
        $this->productoModel = new Producto();
        $this->categoriaModel = new Categoria();
        $this->marcaModel = new Marca();
    }

    public function getDestacados(): array
    {
        return $this->productoModel->obtenerDestacados(8);
    }

    public function getCatalogo(array $filtros = []): array
    {
        return $this->productoModel->obtenerCatalogo($filtros);
    }

    public function getDetalle(int $id): ?array
    {
        return $this->productoModel->obtenerPorId($id);
    }

    // Datos utilizados por RF16.
    public function getProductosAdmin(array $filtros = []): array
    {
        return $this->productoModel->obtenerTodosAdmin($filtros);
    }

    public function getCatalogosAdmin(): array
    {
        return [
            'categorias' => $this->categoriaModel->obtenerTodas(),
            'marcas'     => $this->marcaModel->obtenerTodas(),
            'estados'    => $this->productoModel->obtenerEstados(),
        ];
    }

    public function crearProductoAdmin(array $entrada, ?string $imagen = null): int
    {
        $datos = $this->validarYNormalizar($entrada, null, $imagen);

        if ($this->productoModel->existeCodigoModelo($datos['codigo_modelo'])) {
            throw new InvalidArgumentException('El código/modelo ya está registrado.');
        }

        return $this->productoModel->crear($datos);
    }

    public function actualizarProductoAdmin(int $idProducto, array $entrada, ?string $imagenNueva = null): bool
    {
        if ($idProducto <= 0) {
            throw new InvalidArgumentException('Producto inválido.');
        }

        $actual = $this->productoModel->obtenerPorId($idProducto);
        if (!$actual) {
            throw new RuntimeException('El producto no existe.');
        }

        $imagenFinal = $imagenNueva !== null ? $imagenNueva : ($actual['imagen'] ?? null);
        $datos = $this->validarYNormalizar($entrada, $idProducto, $imagenFinal);

        if ($this->productoModel->existeCodigoModelo($datos['codigo_modelo'], $idProducto)) {
            throw new InvalidArgumentException('El código/modelo ya pertenece a otro producto.');
        }

        return $this->productoModel->actualizar($idProducto, $datos);
    }

    public function eliminarProductoAdmin(int $idProducto): bool
    {
        if ($idProducto <= 0) {
            throw new InvalidArgumentException('Producto inválido.');
        }

        if (!$this->productoModel->obtenerPorId($idProducto)) {
            throw new RuntimeException('El producto no existe.');
        }

        return $this->productoModel->descontinuar($idProducto);
    }

    private function validarYNormalizar(array $entrada, ?int $idProducto, ?string $imagen): array
    {
        $nombre = trim((string)($entrada['nombre'] ?? ''));
        $codigoModelo = trim((string)($entrada['codigo_modelo'] ?? ''));
        $descripcion = trim((string)($entrada['descripcion'] ?? ''));
        $especificaciones = trim((string)($entrada['especificaciones'] ?? ''));

        $idCategoria = filter_var($entrada['id_categoria'] ?? null, FILTER_VALIDATE_INT);
        $idMarca = filter_var($entrada['id_marca'] ?? null, FILTER_VALIDATE_INT);
        $idEstado = filter_var($entrada['id_estado_producto'] ?? 1, FILTER_VALIDATE_INT);
        $stock = filter_var($entrada['stock'] ?? null, FILTER_VALIDATE_INT);
        $precio = filter_var($entrada['precio'] ?? null, FILTER_VALIDATE_FLOAT);
        $destacado = !empty($entrada['destacado']) ? 1 : 0;

        if ($nombre === '' || mb_strlen($nombre) > 200) {
            throw new InvalidArgumentException('Ingrese un nombre válido de hasta 200 caracteres.');
        }

        if ($codigoModelo === '' || mb_strlen($codigoModelo) > 50) {
            throw new InvalidArgumentException('Ingrese un código/modelo válido de hasta 50 caracteres.');
        }

        if (!preg_match('/^[A-Za-z0-9._+\/-]{2,50}$/', $codigoModelo)) {
            throw new InvalidArgumentException('El código/modelo contiene caracteres no permitidos.');
        }

        if ($idCategoria === false || $idCategoria <= 0) {
            throw new InvalidArgumentException('Seleccione una categoría válida.');
        }

        if ($idMarca === false || $idMarca <= 0) {
            throw new InvalidArgumentException('Seleccione una marca válida.');
        }

        if ($idEstado === false || !in_array((int)$idEstado, [1, 2, 3], true)) {
            throw new InvalidArgumentException('Seleccione un estado válido.');
        }

        if ($precio === false || $precio < 0) {
            throw new InvalidArgumentException('El precio debe ser un valor mayor o igual a cero.');
        }

        if ($stock === false || $stock < 0) {
            throw new InvalidArgumentException('El stock debe ser un número entero mayor o igual a cero.');
        }

        if (mb_strlen($descripcion) > 5000 || mb_strlen($especificaciones) > 5000) {
            throw new InvalidArgumentException('La descripción o especificaciones son demasiado extensas.');
        }

        return [
            'id_categoria'        => (int)$idCategoria,
            'id_marca'            => (int)$idMarca,
            'id_estado_producto'  => (int)$idEstado,
            'codigo_modelo'       => $codigoModelo,
            'nombre'              => $nombre,
            'descripcion'         => $descripcion !== '' ? $descripcion : null,
            'especificaciones'    => $especificaciones !== '' ? $especificaciones : null,
            'precio'              => round((float)$precio, 2),
            'stock'               => (int)$stock,
            'imagen'              => $imagen,
            'destacado'           => $destacado,
        ];
    }
}
