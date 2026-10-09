<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Categoria;
use InvalidArgumentException;
use RuntimeException;

class CategoriaController
{
    private Categoria $categoriaModel;

    public function __construct()
    {
        $this->categoriaModel = new Categoria();
    }
    public function getTodas(): array
    {
        return $this->categoriaModel->obtenerTodas();
    }

    public function getCategoriasAdmin(array $filtros = []): array
    {
        return $this->categoriaModel->obtenerTodasAdmin($filtros);
    }

    public function getDetalleAdmin(int $idCategoria): ?array
    {
        if ($idCategoria <= 0) {
            throw new InvalidArgumentException('Categoría inválida.');
        }

        return $this->categoriaModel->obtenerPorId($idCategoria);
    }

    public function crearCategoriaAdmin(array $entrada, ?string $imagen = null): int
    {
        $datos = $this->validarYNormalizar($entrada, $imagen);

        if ($this->categoriaModel->existeNombre($datos['nombre_categoria'])) {
            throw new InvalidArgumentException('Ya existe una categoría con ese nombre.');
        }

        return $this->categoriaModel->crear($datos);
    }

    public function actualizarCategoriaAdmin(int $idCategoria, array $entrada, ?string $imagenNueva = null): bool
    {
        if ($idCategoria <= 0) {
            throw new InvalidArgumentException('Categoría inválida.');
        }

        $actual = $this->categoriaModel->obtenerPorId($idCategoria);
        if (!$actual) {
            throw new RuntimeException('La categoría no existe.');
        }

        $imagenFinal = $imagenNueva !== null
            ? $imagenNueva
            : ($actual['imagen'] ?? null);

        $datos = $this->validarYNormalizar($entrada, $imagenFinal);

        if ($this->categoriaModel->existeNombre($datos['nombre_categoria'], $idCategoria)) {
            throw new InvalidArgumentException('Ya existe otra categoría con ese nombre.');
        }

        return $this->categoriaModel->actualizar($idCategoria, $datos);
    }

    public function cambiarEstadoAdmin(int $idCategoria, int $activo): bool
    {
        if ($idCategoria <= 0) {
            throw new InvalidArgumentException('Categoría inválida.');
        }

        if (!in_array($activo, [0, 1], true)) {
            throw new InvalidArgumentException('Estado de categoría inválido.');
        }

        if (!$this->categoriaModel->obtenerPorId($idCategoria)) {
            throw new RuntimeException('La categoría no existe.');
        }

        return $this->categoriaModel->cambiarEstado($idCategoria, $activo);
    }

    public function eliminarCategoriaAdmin(int $idCategoria): bool
    {
        if ($idCategoria <= 0) {
            throw new InvalidArgumentException('Categoría inválida.');
        }

        if (!$this->categoriaModel->obtenerPorId($idCategoria)) {
            throw new RuntimeException('La categoría no existe.');
        }

        if ($this->categoriaModel->contarProductos($idCategoria) > 0) {
            throw new InvalidArgumentException(
                'No se puede eliminar esta categoría porque tiene productos vinculados, incluso si están descontinuados.. Puede desactivarla.'
            );
        }

        return $this->categoriaModel->eliminar($idCategoria);
    }

    private function validarYNormalizar(array $entrada, ?string $imagen): array
    {
        $nombre = trim((string)($entrada['nombre_categoria'] ?? ''));
        $descripcion = trim((string)($entrada['descripcion'] ?? ''));
        $activo = filter_var($entrada['activo'] ?? 1, FILTER_VALIDATE_INT);

        if ($nombre === '' || mb_strlen($nombre) < 2 || mb_strlen($nombre) > 100) {
            throw new InvalidArgumentException(
                'Ingrese un nombre de categoría válido de 2 a 100 caracteres.'
            );
        }

        if (mb_strlen($descripcion) > 3000) {
            throw new InvalidArgumentException(
                'La descripción no puede superar los 3000 caracteres.'
            );
        }

        if ($activo === false || !in_array((int)$activo, [0, 1], true)) {
            throw new InvalidArgumentException('Seleccione un estado válido.');
        }

        return [
            'nombre_categoria' => $nombre,
            'descripcion' => $descripcion !== '' ? $descripcion : null,
            'imagen' => $imagen,
            'activo' => (int)$activo,
        ];
    }
}
