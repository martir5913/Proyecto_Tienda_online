<?php
/**
 * Modelo de Producto (Electrodomésticos y Línea Blanca)
 * Maneja catálogo, filtros por marca/precio/categoría y operaciones CRUD.
 */

namespace App\Models;

use PDO;

class Producto extends Model
{
    /**
     * Obtiene el listado de productos con filtros dinámicos
     */
    public function obtenerCatalogo(array $filtros = []): array
    {
        $sql = "SELECT p.*, c.nombre_categoria, m.nombre_marca, ep.nombre_estado as estado_nombre,
                       COALESCE(AVG(r.calificacion), 0) as promedio_calificacion,
                       COUNT(r.id_resena) as total_resenas
                FROM productos p
                INNER JOIN categorias c ON p.id_categoria = c.id_categoria
                INNER JOIN marcas m ON p.id_marca = m.id_marca
                INNER JOIN estados_producto ep ON p.id_estado_producto = ep.id_estado_producto
                LEFT JOIN resenas r ON p.id_producto = r.id_producto
                WHERE p.id_estado_producto = 1"; // Solo productos disponibles

        $params = [];

        if (!empty($filtros['categoria'])) {
            $sql .= " AND p.id_categoria = :categoria";
            $params[':categoria'] = (int)$filtros['categoria'];
        }

        if (!empty($filtros['marca'])) {
            $sql .= " AND p.id_marca = :marca";
            $params[':marca'] = (int)$filtros['marca'];
        }

        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (p.nombre LIKE :busqueda OR p.descripcion LIKE :busqueda OR p.codigo_modelo LIKE :busqueda)";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        if (!empty($filtros['precio_min'])) {
            $sql .= " AND p.precio >= :precio_min";
            $params[':precio_min'] = (float)$filtros['precio_min'];
        }

        if (!empty($filtros['precio_max'])) {
            $sql .= " AND p.precio <= :precio_max";
            $params[':precio_max'] = (float)$filtros['precio_max'];
        }

        $sql .= " GROUP BY p.id_producto ORDER BY p.id_producto DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un producto por su ID incluyendo marca y categoría
     */
    public function obtenerPorId(int $idProducto): ?array
    {
        $sql = "SELECT p.*, c.nombre_categoria, m.nombre_marca, ep.nombre_estado as estado_nombre
                FROM productos p
                INNER JOIN categorias c ON p.id_categoria = c.id_categoria
                INNER JOIN marcas m ON p.id_marca = m.id_marca
                INNER JOIN estados_producto ep ON p.id_estado_producto = ep.id_estado_producto
                WHERE p.id_producto = :id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idProducto]);
        $producto = $stmt->fetch();
        return $producto ?: null;
    }

    /**
     * Obtiene productos destacados para la portada (Home)
     */
    public function obtenerDestacados(int $limite = 6): array
    {
        $sql = "SELECT p.*, c.nombre_categoria, m.nombre_marca 
                FROM productos p
                INNER JOIN categorias c ON p.id_categoria = c.id_categoria
                INNER JOIN marcas m ON p.id_marca = m.id_marca
                WHERE p.destacado = 1 AND p.id_estado_producto = 1
                LIMIT :limite";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Guarda un nuevo producto (Admin)
     */
    public function crear(array $datos): int
    {
        $sql = "INSERT INTO productos (id_categoria, id_marca, id_estado_producto, codigo_modelo, nombre, descripcion, especificaciones, precio, stock, imagen, destacado)
                VALUES (:id_categoria, :id_marca, :id_estado_producto, :codigo_modelo, :nombre, :descripcion, :especificaciones, :precio, :stock, :imagen, :destacado)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($datos);
        return (int)$this->db->lastInsertId();
    }
}
