<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Categoria extends Model
{
    public function obtenerTodas(): array
    {
        $stmt = $this->db->query(
            "SELECT c.id_categoria, c.nombre_categoria, c.descripcion, c.imagen, c.activo,
                    COUNT(p.id_producto) AS total_productos,
                    COALESCE(SUM(p.stock), 0)AS stock_total
             FROM categorias c
             LEFT JOIN productos p
                    ON c.id_categoria = p.id_categoria
                   AND p.id_estado_producto = 1
             WHERE c.activo = 1
             GROUP BY c.id_categoria, c.nombre_categoria, c.descripcion, c.imagen, c.activo
             ORDER BY c.nombre_categoria ASC"
        );

        return $stmt->fetchAll();
    }

    /**
     * Listado administrativo, incluyendo categorías inactivas.
     */
    public function obtenerTodasAdmin(array $filtros = []): array
    {
        $sql = 'SELECT c.id_categoria,
                       c.nombre_categoria,
                       c.descripcion,
                       c.imagen,
                       c.activo,
                       COUNT(p.id_producto) AS total_productos,
                       COALESCE(SUM(CASE WHEN p.id_estado_producto <> 3
                                    THEN p.stock
                                    ELSE 0
                                END
                            ),
                            0
                        ) AS stock_total
                        
                FROM categorias c
                LEFT JOIN productos p ON p.id_categoria = c.id_categoria
                WHERE 1 = 1';

        $params = [];

        $busqueda = trim((string)($filtros['busqueda'] ?? ''));
        if ($busqueda !== '') {
            $busqueda = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $busqueda);
            $sql .= " AND (c.nombre_categoria LIKE :busqueda ESCAPE '\\\\'
                           OR COALESCE(c.descripcion, '') LIKE :busqueda ESCAPE '\\\\')";
            $params[':busqueda'] = ['%' . $busqueda . '%', PDO::PARAM_STR];
        }

        $estado = $filtros['estado'] ?? null;
        if ($estado !== null && $estado !== '') {
            $estado = filter_var($estado, FILTER_VALIDATE_INT);
            if ($estado !== false && in_array((int)$estado, [0, 1], true)) {
                $sql .= ' AND c.activo = :activo';
                $params[':activo'] = [(int)$estado, PDO::PARAM_INT];
            }
        }

        $sql .= ' GROUP BY c.id_categoria, c.nombre_categoria, c.descripcion, c.imagen, c.activo
                  ORDER BY c.nombre_categoria ASC';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $marcador => [$valor, $tipo]) {
            $stmt->bindValue($marcador, $valor, $tipo);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $idCategoria): ?array
    {
        if ($idCategoria <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT c.id_categoria,
                    c.nombre_categoria,
                    c.descripcion,
                    c.imagen,
                    c.activo,
                    COUNT(p.id_producto) AS total_productos
             FROM categorias c
             LEFT JOIN productos p ON p.id_categoria = c.id_categoria
             WHERE c.id_categoria = :id_categoria
             GROUP BY c.id_categoria, c.nombre_categoria, c.descripcion, c.imagen, c.activo

             LIMIT 1'
        );
        $stmt->bindValue(':id_categoria', $idCategoria, PDO::PARAM_INT);
        $stmt->execute();

        $categoria = $stmt->fetch();
        return $categoria ?: null;
    }

    public function existeNombre(string $nombre, ?int $excluirId = null): bool
    {
        $sql = 'SELECT 1
                FROM categorias
                WHERE LOWER(nombre_categoria) = LOWER(:nombre_categoria)';

        if ($excluirId !== null && $excluirId > 0) {
            $sql .= ' AND id_categoria <> :id_categoria';
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nombre_categoria', $nombre, PDO::PARAM_STR);

        if ($excluirId !== null && $excluirId > 0) {
            $stmt->bindValue(':id_categoria', $excluirId, PDO::PARAM_INT);
        }

        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO categorias
                (nombre_categoria, descripcion, imagen, activo)
             VALUES
                (:nombre_categoria, :descripcion, :imagen, :activo)'
        );

        $stmt->bindValue(':nombre_categoria', $datos['nombre_categoria'], PDO::PARAM_STR);
        $stmt->bindValue(
            ':descripcion',
            $datos['descripcion'],
            $datos['descripcion'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR
        );
        $stmt->bindValue(
            ':imagen',
            $datos['imagen'],
            $datos['imagen'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR
        );
        $stmt->bindValue(':activo', $datos['activo'], PDO::PARAM_INT);
        $stmt->execute();

        return (int)$this->db->lastInsertId();
    }

    public function actualizar(int $idCategoria, array $datos): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE categorias
             SET nombre_categoria = :nombre_categoria,
                 descripcion = :descripcion,
                 imagen = :imagen,
                 activo = :activo
             WHERE id_categoria = :id_categoria'
        );

        $stmt->bindValue(':nombre_categoria', $datos['nombre_categoria'], PDO::PARAM_STR);
        $stmt->bindValue(
            ':descripcion',
            $datos['descripcion'],
            $datos['descripcion'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR
        );
        $stmt->bindValue(
            ':imagen',
            $datos['imagen'],
            $datos['imagen'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR
        );
        $stmt->bindValue(':activo', $datos['activo'], PDO::PARAM_INT);
        $stmt->bindValue(':id_categoria', $idCategoria, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function cambiarEstado(int $idCategoria, int $activo): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE categorias
             SET activo = :activo
             WHERE id_categoria = :id_categoria'
        );
        $stmt->bindValue(':activo', $activo, PDO::PARAM_INT);
        $stmt->bindValue(':id_categoria', $idCategoria, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function contarProductos(int $idCategoria): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM productos
             WHERE id_categoria = :id_categoria'
        );
        $stmt->bindValue(':id_categoria', $idCategoria, PDO::PARAM_INT);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public function eliminar(int $idCategoria): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM categorias
             WHERE id_categoria = :id_categoria'
        );
        $stmt->bindValue(':id_categoria', $idCategoria, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
