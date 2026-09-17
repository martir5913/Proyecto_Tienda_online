<?php
 //* Modelo de Reseñas y Calificaciones
 

namespace App\Models;

use PDO;

class Resena extends Model
{
    // * Obtiene las reseñas aprobadas de un producto
    
    public function obtenerPorProducto(int $idProducto): array
    {
        $stmt = $this->db->prepare(
            "SELECT r.*, u.nombre, u.apellido
             FROM resenas r
             INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
             WHERE r.id_producto = :id
             ORDER BY r.fecha DESC"
        );
        $stmt->execute([':id' => $idProducto]);
        return $stmt->fetchAll();
    }

     // * Registra una nueva reseña
     
    public function crear(int $idUsuario, int $idProducto, int $calificacion, string $comentario): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO resenas (id_usuario, id_producto, calificacion, comentario)
             VALUES (:usuario, :producto, :calificacion, :comentario)"
        );
        return $stmt->execute([
            ':usuario'      => $idUsuario,
            ':producto'     => $idProducto,
            ':calificacion' => $calificacion,
            ':comentario'   => $comentario
        ]);
    }
}
