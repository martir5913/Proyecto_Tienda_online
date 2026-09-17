<?php
 // * Modelo de Pedido
 // * Implementa transacciones ACID en la creación de órdenes y reducción de inventario.
 

namespace App\Models;

use Exception;
use PDO;

class Pedido extends Model
{
     // * Registra un pedido transaccional garantizando propiedades ACID
     
    public function crearPedidoTransaccional(int $idUsuario, int $idMetodoPago, string $direccionEnvio, array $itemsCarrito, string $notas = ''): array
    {
        if (empty($itemsCarrito)) {
            throw new Exception("El carrito de compras está vacío.");
        }

        try {
            // Iniciar Transacción ACID
            $this->db->beginTransaction();

            $subtotalAcumulado = 0.0;
            $itemsValidados = [];

            // 1. Validar existencia y stock de cada producto con bloqueo de fila (FOR UPDATE)
            $stmtProducto = $this->db->prepare(
                "SELECT id_producto, nombre, precio, stock FROM productos WHERE id_producto = :id FOR UPDATE"
            );

            foreach ($itemsCarrito as $item) {
                $idProducto = (int)$item['id_producto'];
                $cantidadSolicitada = (int)$item['cantidad'];

                $stmtProducto->execute([':id' => $idProducto]);
                $producto = $stmtProducto->fetch();

                if (!$producto) {
                    throw new Exception("El producto con ID {$idProducto} no existe.");
                }

                if ($producto['stock'] < $cantidadSolicitada) {
                    throw new Exception("Stock insuficiente para '{$producto['nombre']}'. Disponibles: {$producto['stock']}.");
                }

                $precioUnitario = (float)$producto['precio'];
                $subtotalLinea = $precioUnitario * $cantidadSolicitada;
                $subtotalAcumulado += $subtotalLinea;

                $itemsValidados[] = [
                    'id_producto'     => $idProducto,
                    'cantidad'        => $cantidadSolicitada,
                    'precio_unitario' => $precioUnitario,
                    'subtotal'        => $subtotalLinea
                ];
            }

            // 2. Calcular totales (12% IVA incluido o calculado)
            $impuesto = round($subtotalAcumulado * 0.12, 2);
            $total = $subtotalAcumulado + $impuesto;
            $numeroPedido = 'PED-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

            // 3. Insertar cabecera de Pedido
            $stmtPedido = $this->db->prepare(
                "INSERT INTO pedidos (numero_pedido, id_usuario, id_metodo_pago, id_estado_pedido, subtotal, impuesto, total, direccion_envio, notas)
                 VALUES (:numero, :id_usuario, :id_metodo, 1, :subtotal, :impuesto, :total, :direccion, :notas)"
            );
            $stmtPedido->execute([
                ':numero'     => $numeroPedido,
                ':id_usuario' => $idUsuario,
                ':id_metodo'  => $idMetodoPago,
                ':subtotal'   => $subtotalAcumulado,
                ':impuesto'   => $impuesto,
                ':total'      => $total,
                ':direccion'  => $direccionEnvio,
                ':notas'      => $notas
            ]);

            $idPedido = (int)$this->db->lastInsertId();

            // 4. Insertar detalle de productos y descontar inventario
            $stmtDetalle = $this->db->prepare(
                "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, subtotal)
                 VALUES (:id_pedido, :id_producto, :cantidad, :precio, :subtotal)"
            );

            $stmtUpdateStock = $this->db->prepare(
                "UPDATE productos SET stock = stock - :cantidad WHERE id_producto = :id"
            );

            foreach ($itemsValidados as $val) {
                $stmtDetalle->execute([
                    ':id_pedido'   => $idPedido,
                    ':id_producto' => $val['id_producto'],
                    ':cantidad'    => $val['cantidad'],
                    ':precio'      => $val['precio_unitario'],
                    ':subtotal'    => $val['subtotal']
                ]);

                $stmtUpdateStock->execute([
                    ':cantidad' => $val['cantidad'],
                    ':id'       => $val['id_producto']
                ]);
            }

            // Confirmar transacción ACID
            $this->db->commit();

            return [
                'id_pedido'     => $idPedido,
                'numero_pedido' => $numeroPedido,
                'total'         => $total
            ];

        } catch (Exception $e) {
            // Revertir ante cualquier fallo
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

     // * Obtiene los pedidos realizados por un cliente específico
     
    public function obtenerPorUsuario(int $idUsuario): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, ep.nombre_estado, mp.nombre_metodo, COUNT(dp.id_detalle) as total_articulos
             FROM pedidos p
             INNER JOIN estados_pedido ep ON p.id_estado_pedido = ep.id_estado_pedido
             INNER JOIN metodos_pago mp ON p.id_metodo_pago = mp.id_metodo_pago
             LEFT JOIN detalle_pedido dp ON p.id_pedido = dp.id_pedido
             WHERE p.id_usuario = :id_usuario
             GROUP BY p.id_pedido
             ORDER BY p.id_pedido DESC"
        );
        $stmt->execute([':id_usuario' => $idUsuario]);
        return $stmt->fetchAll();
    }
}
