<?php
/**
 * Modelo de Categoria
 */

namespace App\Models;

use PDO;

class Categoria extends Model
{
    /**
     * Obtiene todas las categorías activas
     */
    public function obtenerTodas(): array
    {
        $stmt = $this->db->query(
            "SELECT c.*, COUNT(p.id_producto) as total_productos
             FROM categorias c
             LEFT JOIN productos p ON c.id_categoria = p.id_categoria AND p.id_estado_producto = 1
             WHERE c.activo = 1
             GROUP BY c.id_categoria
             ORDER BY c.nombre_categoria ASC"
        );
        return $stmt->fetchAll();
    }
}
