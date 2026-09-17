<?php
 //* Modelo de Wishlist (Lista de Deseos)
 

namespace App\Models;

use PDO;

class Wishlist extends Model
{
     // * Agrega un producto a la lista de deseos del usuario
     
    public function agregar(int $idUsuario, int $idProducto): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO wishlist (id_usuario, id_producto) 
             VALUES (:usuario, :producto)
             ON DUPLICATE KEY UPDATE fecha_agregado = CURRENT_TIMESTAMP"
        );
        return $stmt->execute([
            ':usuario'  => $idUsuario,
            ':producto' => $idProducto
        ]);
    }

     // * Elimina un producto de la lista de deseos
     
    public function eliminar(int $idUsuario, int $idProducto): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM wishlist WHERE id_usuario = :usuario AND id_producto = :producto"
        );
        return $stmt->execute([
            ':usuario'  => $idUsuario,
            ':producto' => $idProducto
        ]);
    }

     // * Obtiene los productos en la lista de deseos del usuario
     
    public function obtenerPorUsuario(int $idUsuario): array
    {
        $stmt = $this->db->prepare(
            "SELECT w.id_wishlist, w.fecha_agregado, p.*, c.nombre_categoria, m.nombre_marca
             FROM wishlist w
             INNER JOIN productos p ON w.id_producto = p.id_producto
             INNER JOIN categorias c ON p.id_categoria = c.id_categoria
             INNER JOIN marcas m ON p.id_marca = m.id_marca
             WHERE w.id_usuario = :usuario
             ORDER BY w.fecha_agregado DESC"
        );
        $stmt->execute([':usuario' => $idUsuario]);
        return $stmt->fetchAll();
    }
}
