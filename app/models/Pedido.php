<?php

declare(strict_types=1);

namespace App\Models;

use DomainException;
use InvalidArgumentException;
use PDO;
use PDOStatement;
use Throwable;

class Pedido extends Model
{
    private const ESTADO_PEDIDO_PENDIENTE = 1;
    private const ESTADO_PRODUCTO_DISPONIBLE = 1;
    private const ESTADO_PRODUCTO_AGOTADO = 2;
    private const METODO_PAGO_ACTIVO = 1;
    private const CANTIDAD_MAXIMA_POR_PRODUCTO = 20;
    private const ITEMS_MAXIMOS_POR_PEDIDO = 100;
    private const DIRECCION_MAXIMA = 500;
    private const NOTAS_MAXIMAS = 1000;

    /**
     * Registra un pedido completo dentro de una sola transacción ACID.
     *
     * Valida nuevamente precio, disponibilidad y stock desde la base de datos.
     * Nunca confía en precios o totales almacenados en la sesión del carrito.
     */
    public function crearPedidoTransaccional(
        int $idUsuario,
        int $idMetodoPago,
        string $direccionEnvio,
        array $itemsCarrito,
        string $notas = ''
    ): array {
        $idUsuario = $this->validarId($idUsuario, 'usuario');
        $idMetodoPago = $this->validarId($idMetodoPago, 'método de pago');
        $direccionEnvio = $this->normalizarTextoObligatorio(
            $direccionEnvio,
            'dirección de entrega',
            self::DIRECCION_MAXIMA
        );
        $notas = $this->normalizarTextoOpcional($notas, self::NOTAS_MAXIMAS);
        $items = $this->normalizarItems($itemsCarrito);

        if ($items === []) {
            throw new InvalidArgumentException('El carrito de compras está vacío.');
        }

        $this->db->beginTransaction();

        try {
            $this->validarMetodoPagoActivo($idMetodoPago);

            $stmtProducto = $this->db->prepare(
                'SELECT id_producto, nombre, precio, stock, id_estado_producto
                 FROM productos
                 WHERE id_producto = :id_producto
                 LIMIT 1
                 FOR UPDATE'
            );

            $subtotalCentavos = 0;
            $itemsValidados = [];

            foreach ($items as $item) {
                $stmtProducto->bindValue(':id_producto', $item['id_producto'], PDO::PARAM_INT);
                $stmtProducto->execute();

                $producto = $stmtProducto->fetch(PDO::FETCH_ASSOC);

                if (!$producto) {
                    throw new DomainException(
                        'Uno de los productos seleccionados ya no existe.'
                    );
                }

                if ((int)$producto['id_estado_producto'] !== self::ESTADO_PRODUCTO_DISPONIBLE) {
                    throw new DomainException(
                        "El producto '{$producto['nombre']}' ya no está disponible."
                    );
                }

                $stockDisponible = (int)$producto['stock'];

                if ($stockDisponible < $item['cantidad']) {
                    throw new DomainException(
                        "Stock insuficiente para '{$producto['nombre']}'. Disponibles: {$stockDisponible}."
                    );
                }

                $precioCentavos = (int)round(((float)$producto['precio']) * 100);
                $subtotalLineaCentavos = $precioCentavos * $item['cantidad'];
                $subtotalCentavos += $subtotalLineaCentavos;

                $itemsValidados[] = [
                    'id_producto' => (int)$producto['id_producto'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $this->centavosADecimal($precioCentavos),
                    'subtotal' => $this->centavosADecimal($subtotalLineaCentavos),
                ];
            }

            $impuestoCentavos = (int)round($subtotalCentavos * 0.12);
            $totalCentavos = $subtotalCentavos + $impuestoCentavos;

            $subtotal = $this->centavosADecimal($subtotalCentavos);
            $impuesto = $this->centavosADecimal($impuestoCentavos);
            $total = $this->centavosADecimal($totalCentavos);
            $numeroPedido = $this->generarNumeroPedido();

            $stmtPedido = $this->db->prepare(
                'INSERT INTO pedidos
                    (numero_pedido, id_usuario, id_metodo_pago, id_estado_pedido,
                     subtotal, impuesto, total, direccion_envio, notas)
                 VALUES
                    (:numero_pedido, :id_usuario, :id_metodo_pago, :id_estado_pedido,
                     :subtotal, :impuesto, :total, :direccion_envio, :notas)'
            );

            $stmtPedido->bindValue(':numero_pedido', $numeroPedido, PDO::PARAM_STR);
            $stmtPedido->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmtPedido->bindValue(':id_metodo_pago', $idMetodoPago, PDO::PARAM_INT);
            $stmtPedido->bindValue(':id_estado_pedido', self::ESTADO_PEDIDO_PENDIENTE, PDO::PARAM_INT);
            $stmtPedido->bindValue(':subtotal', $subtotal, PDO::PARAM_STR);
            $stmtPedido->bindValue(':impuesto', $impuesto, PDO::PARAM_STR);
            $stmtPedido->bindValue(':total', $total, PDO::PARAM_STR);
            $stmtPedido->bindValue(':direccion_envio', $direccionEnvio, PDO::PARAM_STR);

            if ($notas === '') {
                $stmtPedido->bindValue(':notas', null, PDO::PARAM_NULL);
            } else {
                $stmtPedido->bindValue(':notas', $notas, PDO::PARAM_STR);
            }

            $stmtPedido->execute();

            $idPedido = (int)$this->db->lastInsertId();

            if ($idPedido <= 0) {
                throw new DomainException('No fue posible generar el pedido.');
            }

            $stmtDetalle = $this->db->prepare(
                'INSERT INTO detalle_pedido
                    (id_pedido, id_producto, cantidad, precio_unitario, subtotal)
                 VALUES
                    (:id_pedido, :id_producto, :cantidad, :precio_unitario, :subtotal)'
            );

            $stmtStock = $this->db->prepare(
                'UPDATE productos
                 SET stock = stock - :cantidad_resta
                 WHERE id_producto = :id_producto
                   AND stock >= :cantidad_minima
                 LIMIT 1'
            );

            $stmtAgotado = $this->db->prepare(
                'UPDATE productos
                 SET id_estado_producto = :estado_agotado
                 WHERE id_producto = :id_producto
                   AND stock = 0
                 LIMIT 1'
            );

            foreach ($itemsValidados as $item) {
                $stmtDetalle->bindValue(':id_pedido', $idPedido, PDO::PARAM_INT);
                $stmtDetalle->bindValue(':id_producto', $item['id_producto'], PDO::PARAM_INT);
                $stmtDetalle->bindValue(':cantidad', $item['cantidad'], PDO::PARAM_INT);
                $stmtDetalle->bindValue(':precio_unitario', $item['precio_unitario'], PDO::PARAM_STR);
                $stmtDetalle->bindValue(':subtotal', $item['subtotal'], PDO::PARAM_STR);
                $stmtDetalle->execute();

                $stmtStock->bindValue(':cantidad_resta', $item['cantidad'], PDO::PARAM_INT);
                $stmtStock->bindValue(':id_producto', $item['id_producto'], PDO::PARAM_INT);
                $stmtStock->bindValue(':cantidad_minima', $item['cantidad'], PDO::PARAM_INT);
                $stmtStock->execute();

                if ($stmtStock->rowCount() !== 1) {
                    throw new DomainException(
                        'El stock cambió durante la compra. Actualiza el carrito e inténtalo nuevamente.'
                    );
                }

                $stmtAgotado->bindValue(':estado_agotado', self::ESTADO_PRODUCTO_AGOTADO, PDO::PARAM_INT);
                $stmtAgotado->bindValue(':id_producto', $item['id_producto'], PDO::PARAM_INT);
                $stmtAgotado->execute();
            }

            $this->db->commit();

            return [
                'id_pedido' => $idPedido,
                'numero_pedido' => $numeroPedido,
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'total' => $total,
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Obtiene los pedidos de un usuario.
     * Esta consulta también es utilizada posteriormente por RF12.
     */
    public function obtenerPorUsuario(int $idUsuario): array
    {
        $idUsuario = $this->validarId($idUsuario, 'usuario');

        $sql = 'SELECT p.id_pedido, p.numero_pedido, p.fecha_pedido,
                       p.subtotal, p.impuesto, p.total, p.direccion_envio,
                       ep.nombre_estado, mp.nombre_metodo,
                       COALESCE((
                           SELECT SUM(dp.cantidad)
                           FROM detalle_pedido dp
                           WHERE dp.id_pedido = p.id_pedido
                       ), 0) AS total_articulos
                FROM pedidos p
                INNER JOIN estados_pedido ep
                    ON p.id_estado_pedido = ep.id_estado_pedido
                INNER JOIN metodos_pago mp
                    ON p.id_metodo_pago = mp.id_metodo_pago
                WHERE p.id_usuario = :id_usuario
                ORDER BY p.id_pedido DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function validarMetodoPagoActivo(int $idMetodoPago): void
    {
        $stmt = $this->db->prepare(
            'SELECT id_metodo_pago
             FROM metodos_pago
             WHERE id_metodo_pago = :id_metodo_pago
               AND activo = :activo
             LIMIT 1'
        );

        $stmt->bindValue(':id_metodo_pago', $idMetodoPago, PDO::PARAM_INT);
        $stmt->bindValue(':activo', self::METODO_PAGO_ACTIVO, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->fetchColumn() === false) {
            throw new InvalidArgumentException('El método de pago seleccionado no es válido.');
        }
    }

    private function normalizarItems(array $items): array
    {
        if (count($items) > self::ITEMS_MAXIMOS_POR_PEDIDO) {
            throw new InvalidArgumentException('El pedido contiene demasiados productos.');
        }

        $normalizados = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                throw new InvalidArgumentException('El carrito contiene información inválida.');
            }

            $idProducto = filter_var(
                $item['id_producto'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            $cantidad = filter_var(
                $item['cantidad'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => [
                    'min_range' => 1,
                    'max_range' => self::CANTIDAD_MAXIMA_POR_PRODUCTO,
                ]]
            );

            if ($idProducto === false || $cantidad === false) {
                throw new InvalidArgumentException('El carrito contiene cantidades o productos inválidos.');
            }

            $idProducto = (int)$idProducto;
            $cantidad = (int)$cantidad;

            if (isset($normalizados[$idProducto])) {
                $cantidad += $normalizados[$idProducto]['cantidad'];

                if ($cantidad > self::CANTIDAD_MAXIMA_POR_PRODUCTO) {
                    throw new InvalidArgumentException(
                        'La cantidad máxima permitida por producto es ' . self::CANTIDAD_MAXIMA_POR_PRODUCTO . '.'
                    );
                }
            }

            $normalizados[$idProducto] = [
                'id_producto' => $idProducto,
                'cantidad' => $cantidad,
            ];
        }

        return array_values($normalizados);
    }

    private function validarId(int $id, string $campo): int
    {
        if ($id <= 0) {
            throw new InvalidArgumentException("El ID de {$campo} no es válido.");
        }

        return $id;
    }

    private function normalizarTextoObligatorio(
        string $valor,
        string $campo,
        int $longitudMaxima
    ): string {
        $valor = trim($valor);

        if ($valor === '') {
            throw new InvalidArgumentException("La {$campo} es obligatoria.");
        }

        if (mb_strlen($valor, 'UTF-8') > $longitudMaxima) {
            throw new InvalidArgumentException("La {$campo} supera la longitud permitida.");
        }

        return $valor;
    }

    private function normalizarTextoOpcional(string $valor, int $longitudMaxima): string
    {
        $valor = trim($valor);

        if (mb_strlen($valor, 'UTF-8') > $longitudMaxima) {
            throw new InvalidArgumentException('Las notas superan la longitud permitida.');
        }

        return $valor;
    }

    private function centavosADecimal(int $centavos): string
    {
        return number_format($centavos / 100, 2, '.', '');
    }

    private function generarNumeroPedido(): string
    {
        return 'PED-' . date('Ymd-His') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }
}
