<?php
 // * Modelo de Marca
 

namespace App\Models;

use PDO;

class Marca extends Model
{
    // * Obtiene todas las marcas activas con el conteo de sus productos
    // prueba 
    public function obtenerTodas(): array
    {
        $stmt = $this->db->query(
            "SELECT m.*, COUNT(p.id_producto) as total_productos
             FROM marcas m
             LEFT JOIN productos p ON m.id_marca = p.id_marca AND p.id_estado_producto = 1
             WHERE m.activo = 1
             GROUP BY m.id_marca
             ORDER BY m.nombre_marca ASC"
        );
        return $stmt->fetchAll();
    }
}
