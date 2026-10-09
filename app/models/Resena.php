<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Resena extends Model
{
    private const ESTADO_PEDIDO_ENTREGADO = 4;

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
