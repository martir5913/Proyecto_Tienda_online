<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * RF14 - Reseñas y calificaciones.
 *
 * La autorización real para reseñar se valida contra pedidos entregados.
 * El id_usuario siempre proviene de la sesión, nunca del navegador.
 */
class Resena extends Model
{
    private const ESTADO_PEDIDO_ENTREGADO = 4;

    /**
     * Obtiene las reseñas públicas de un producto.
     */
    public function obtenerPorProducto(int $idProducto): array
    {
        if ($idProducto <= 0) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
                r.id_resena,
                r.id_producto,
                r.calificacion,
                r.comentario,
                r.fecha,
                u.nombre AS nombre_usuario
             FROM resenas r
             INNER JOIN usuarios u
                     ON u.id_usuario = r.id_usuario
             WHERE r.id_producto = :id_producto
             ORDER BY r.fecha DESC, r.id_resena DESC'
        );

        $stmt->bindValue(':id_producto', $idProducto, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve promedio y cantidad de reseñas de un producto.
     */
    public function obtenerResumenProducto(int $idProducto): array
    {
        if ($idProducto <= 0) {
            return [
                'promedio' => 0.0,
                'total' => 0,
            ];
        }

        $stmt = $this->db->prepare(
            'SELECT
                COALESCE(AVG(calificacion), 0) AS promedio,
                COUNT(id_resena) AS total
             FROM resenas
             WHERE id_producto = :id_producto'
        );

        $stmt->bindValue(':id_producto', $idProducto, PDO::PARAM_INT);
        $stmt->execute();

        $resumen = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'promedio' => round((float)($resumen['promedio'] ?? 0), 1),
            'total' => (int)($resumen['total'] ?? 0),
        ];
    }

    /**
     * Obtiene la reseña registrada por un usuario para un producto.
     */
    public function obtenerDelUsuario(int $idUsuario, int $idProducto): ?array
    {
        if ($idUsuario <= 0 || $idProducto <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id_resena,
                id_usuario,
                id_producto,
                calificacion,
                comentario,
                fecha
             FROM resenas
             WHERE id_usuario = :id_usuario
               AND id_producto = :id_producto
             ORDER BY id_resena DESC
             LIMIT 1'
        );

        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':id_producto', $idProducto, PDO::PARAM_INT);
        $stmt->execute();

        $resena = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resena ?: null;
    }

    /**
     * Comprueba que el usuario realmente haya adquirido el producto
     * y que al menos uno de sus pedidos se encuentre Entregado.
     */
    public function usuarioPuedeResenar(int $idUsuario, int $idProducto): bool
    {
        if ($idUsuario <= 0 || $idProducto <= 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT 1
             FROM pedidos p
             INNER JOIN detalle_pedido dp
                     ON dp.id_pedido = p.id_pedido
             WHERE p.id_usuario = :id_usuario
               AND dp.id_producto = :id_producto
               AND p.id_estado_pedido = :estado_entregado
             LIMIT 1'
        );

        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':id_producto', $idProducto, PDO::PARAM_INT);
        $stmt->bindValue(':estado_entregado', self::ESTADO_PEDIDO_ENTREGADO, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Registra una nueva reseña.
     * La restricción UNIQUE en MySQL evita duplicados incluso ante concurrencia.
     */
    public function crear(
        int $idUsuario,
        int $idProducto,
        int $calificacion,
        string $comentario
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO resenas
                (id_usuario, id_producto, calificacion, comentario)
             VALUES
                (:id_usuario, :id_producto, :calificacion, :comentario)'
        );

        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':id_producto', $idProducto, PDO::PARAM_INT);
        $stmt->bindValue(':calificacion', $calificacion, PDO::PARAM_INT);
        $stmt->bindValue(':comentario', $comentario, PDO::PARAM_STR);
        $stmt->execute();

        return (int)$this->db->lastInsertId();
    }
}
