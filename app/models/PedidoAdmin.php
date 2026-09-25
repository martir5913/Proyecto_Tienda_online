<?php

declare(strict_types=1);

namespace App\Models;

use DateTime;
use InvalidArgumentException;
use PDO;
use RuntimeException;
use Throwable;

/**
 * Modelo administrativo de pedidos.
 *
 * Se mantiene separado de Pedido.php para no alterar el flujo RF11 del cliente.
 */
class PedidoAdmin extends Model
{
    public const ESTADO_PENDIENTE  = 1;
    public const ESTADO_PROCESANDO = 2;
    public const ESTADO_ENVIADO    = 3;
    public const ESTADO_ENTREGADO  = 4;
    public const ESTADO_CANCELADO  = 5;

    private const LIMITE_LISTADO = 100;

    /**
     * Transiciones permitidas del flujo del pedido.
     * No se permite retroceder un pedido ni cancelar uno ya enviado/entregado.
     */
    private const TRANSICIONES = [
        self::ESTADO_PENDIENTE  => [self::ESTADO_PROCESANDO, self::ESTADO_CANCELADO],
        self::ESTADO_PROCESANDO => [self::ESTADO_ENVIADO, self::ESTADO_CANCELADO],
        self::ESTADO_ENVIADO    => [self::ESTADO_ENTREGADO],
        self::ESTADO_ENTREGADO  => [],
        self::ESTADO_CANCELADO  => [],
    ];

    /** Lista blanca para ORDER BY. */
    private const ORDENES = [
        'recientes'  => 'p.fecha_pedido DESC, p.id_pedido DESC',
        'antiguos'   => 'p.fecha_pedido ASC, p.id_pedido ASC',
        'total_desc' => 'p.total DESC, p.id_pedido DESC',
        'total_asc'  => 'p.total ASC, p.id_pedido DESC',
    ];

    /**
     * Lista pedidos para administración.
     */
    public function obtenerPedidos(array $filtros = []): array
    {
        $filtros = $this->normalizarFiltros($filtros);

        $sql = "SELECT p.id_pedido, p.numero_pedido, p.fecha_pedido, p.subtotal, p.impuesto, p.total,
                       p.id_estado_pedido, u.id_usuario, u.nombre, u.apellido, u.correo,
                       mp.nombre_metodo, ep.nombre_estado,
                       COALESCE(SUM(dp.cantidad), 0) AS total_articulos
                FROM pedidos p
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                INNER JOIN metodos_pago mp ON p.id_metodo_pago = mp.id_metodo_pago
                INNER JOIN estados_pedido ep ON p.id_estado_pedido = ep.id_estado_pedido
                LEFT JOIN detalle_pedido dp ON p.id_pedido = dp.id_pedido
                WHERE 1 = 1";

        $params = [];

        if ($filtros['estado'] !== null) {
            $sql .= " AND p.id_estado_pedido = :estado";
            $params[':estado'] = [$filtros['estado'], PDO::PARAM_INT];
        }

        if ($filtros['busqueda'] !== null) {
            $sql .= " AND (
                        p.numero_pedido LIKE :buscar_numero ESCAPE '!'
                        OR u.nombre LIKE :buscar_nombre ESCAPE '!'
                        OR u.apellido LIKE :buscar_apellido ESCAPE '!'
                        OR u.correo LIKE :buscar_correo ESCAPE '!'
                      )";

            $params[':buscar_numero'] = [$filtros['busqueda'], PDO::PARAM_STR];
            $params[':buscar_nombre'] = [$filtros['busqueda'], PDO::PARAM_STR];
            $params[':buscar_apellido'] = [$filtros['busqueda'], PDO::PARAM_STR];
            $params[':buscar_correo'] = [$filtros['busqueda'], PDO::PARAM_STR];
        }

        if ($filtros['desde'] !== null) {
            $sql .= " AND p.fecha_pedido >= :desde";
            $params[':desde'] = [$filtros['desde'] . ' 00:00:00', PDO::PARAM_STR];
        }

        if ($filtros['hasta'] !== null) {
            $sql .= " AND p.fecha_pedido <= :hasta";
            $params[':hasta'] = [$filtros['hasta'] . ' 23:59:59', PDO::PARAM_STR];
        }

        $sql .= " GROUP BY p.id_pedido, p.numero_pedido, p.fecha_pedido, p.subtotal, p.impuesto, p.total,
                           p.id_estado_pedido, u.id_usuario, u.nombre, u.apellido, u.correo,
                           mp.nombre_metodo, ep.nombre_estado";

        $sql .= " ORDER BY " . self::ORDENES[$filtros['orden']];
        $sql .= " LIMIT :limite";
        $params[':limite'] = [self::LIMITE_LISTADO, PDO::PARAM_INT];

        return $this->ejecutar($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Resumen de pedidos por estado para las tarjetas superiores.
     */
    public function obtenerResumen(): array
    {
        $sql = "SELECT
                    COUNT(*) AS total,
                    COALESCE(SUM(CASE WHEN id_estado_pedido = :pendiente THEN 1 ELSE 0 END), 0) AS pendientes,
                    COALESCE(SUM(CASE WHEN id_estado_pedido = :procesando THEN 1 ELSE 0 END), 0) AS procesando,
                    COALESCE(SUM(CASE WHEN id_estado_pedido = :enviado THEN 1 ELSE 0 END), 0) AS enviados,
                    COALESCE(SUM(CASE WHEN id_estado_pedido = :entregado THEN 1 ELSE 0 END), 0) AS entregados,
                    COALESCE(SUM(CASE WHEN id_estado_pedido = :cancelado THEN 1 ELSE 0 END), 0) AS cancelados
                FROM pedidos";

        $fila = $this->ejecutar($sql, [
            ':pendiente'  => [self::ESTADO_PENDIENTE, PDO::PARAM_INT],
            ':procesando' => [self::ESTADO_PROCESANDO, PDO::PARAM_INT],
            ':enviado'    => [self::ESTADO_ENVIADO, PDO::PARAM_INT],
            ':entregado'  => [self::ESTADO_ENTREGADO, PDO::PARAM_INT],
            ':cancelado'  => [self::ESTADO_CANCELADO, PDO::PARAM_INT],
        ])->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total'      => (int)($fila['total'] ?? 0),
            'pendientes' => (int)($fila['pendientes'] ?? 0),
            'procesando' => (int)($fila['procesando'] ?? 0),
            'enviados'   => (int)($fila['enviados'] ?? 0),
            'entregados' => (int)($fila['entregados'] ?? 0),
            'cancelados' => (int)($fila['cancelados'] ?? 0),
        ];
    }

    /**
     * Devuelve los estados existentes para filtros y referencia visual.
     */
    public function obtenerEstados(): array
    {
        $sql = "SELECT id_estado_pedido, nombre_estado
                FROM estados_pedido
                ORDER BY id_estado_pedido ASC";

        return $this->ejecutar($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene cabecera y detalle completo de un pedido.
     */
    public function obtenerDetalle(int $idPedido): ?array
    {
        $idPedido = $this->validarId($idPedido);

        $sqlPedido = "SELECT p.id_pedido, p.numero_pedido, p.fecha_pedido, p.subtotal, p.impuesto, p.total,
                             p.direccion_envio, p.notas, p.id_estado_pedido,
                             u.id_usuario, u.nombre, u.apellido, u.correo, u.telefono,
                             mp.nombre_metodo, ep.nombre_estado
                      FROM pedidos p
                      INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                      INNER JOIN metodos_pago mp ON p.id_metodo_pago = mp.id_metodo_pago
                      INNER JOIN estados_pedido ep ON p.id_estado_pedido = ep.id_estado_pedido
                      WHERE p.id_pedido = :id_pedido
                      LIMIT 1";

        $pedido = $this->ejecutar($sqlPedido, [
            ':id_pedido' => [$idPedido, PDO::PARAM_INT],
        ])->fetch(PDO::FETCH_ASSOC);

        if (!$pedido) {
            return null;
        }

        $sqlDetalle = "SELECT dp.id_detalle, dp.id_producto, dp.cantidad, dp.precio_unitario, dp.subtotal,
                              pr.nombre AS nombre_producto, pr.codigo_modelo, pr.imagen
                       FROM detalle_pedido dp
                       INNER JOIN productos pr ON dp.id_producto = pr.id_producto
                       WHERE dp.id_pedido = :id_pedido
                       ORDER BY dp.id_detalle ASC";

        $items = $this->ejecutar($sqlDetalle, [
            ':id_pedido' => [$idPedido, PDO::PARAM_INT],
        ])->fetchAll(PDO::FETCH_ASSOC);

        $pedido['items'] = $items;
        return $pedido;
    }

    /**
     * Cambia el estado respetando el flujo definido.
     * Al cancelar Pendiente/Procesando se devuelve el inventario exactamente una vez.
     */
    public function cambiarEstado(int $idPedido, int $nuevoEstado): array
    {
        $idPedido = $this->validarId($idPedido);
        $nuevoEstado = $this->validarEstado($nuevoEstado);

        try {
            $this->db->beginTransaction();

            $stmtPedido = $this->ejecutar(
                "SELECT id_pedido, numero_pedido, id_estado_pedido
                 FROM pedidos
                 WHERE id_pedido = :id_pedido
                 FOR UPDATE",
                [':id_pedido' => [$idPedido, PDO::PARAM_INT]]
            );

            $pedido = $stmtPedido->fetch(PDO::FETCH_ASSOC);

            if (!$pedido) {
                throw new InvalidArgumentException('El pedido solicitado no existe.');
            }

            $estadoActual = (int)$pedido['id_estado_pedido'];

            if ($estadoActual === $nuevoEstado) {
                throw new InvalidArgumentException('El pedido ya se encuentra en ese estado.');
            }

            if (!in_array($nuevoEstado, self::TRANSICIONES[$estadoActual] ?? [], true)) {
                throw new InvalidArgumentException('La transición de estado solicitada no está permitida.');
            }

            if ($nuevoEstado === self::ESTADO_CANCELADO) {
                $this->restaurarInventarioPedido($idPedido);
            }

            $this->ejecutar(
                "UPDATE pedidos
                 SET id_estado_pedido = :nuevo_estado
                 WHERE id_pedido = :id_pedido",
                [
                    ':nuevo_estado' => [$nuevoEstado, PDO::PARAM_INT],
                    ':id_pedido'    => [$idPedido, PDO::PARAM_INT],
                ]
            );

            $this->db->commit();

            return [
                'id_pedido' => $idPedido,
                'numero_pedido' => (string)$pedido['numero_pedido'],
                'id_estado_pedido' => $nuevoEstado,
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Eliminación física restringida exclusivamente a pedidos Cancelados.
     * Se ofrece porque el módulo solicitado incluye "Eliminar".
     * Para pedidos activos/entregados se conserva la trazabilidad.
     */
    public function eliminarCancelado(int $idPedido): bool
    {
        $idPedido = $this->validarId($idPedido);

        try {
            $this->db->beginTransaction();

            $pedido = $this->ejecutar(
                "SELECT id_pedido, id_estado_pedido
                 FROM pedidos
                 WHERE id_pedido = :id_pedido
                 FOR UPDATE",
                [':id_pedido' => [$idPedido, PDO::PARAM_INT]]
            )->fetch(PDO::FETCH_ASSOC);

            if (!$pedido) {
                throw new InvalidArgumentException('El pedido solicitado no existe.');
            }

            if ((int)$pedido['id_estado_pedido'] !== self::ESTADO_CANCELADO) {
                throw new InvalidArgumentException('Solo se pueden eliminar definitivamente pedidos cancelados.');
            }

            $stmt = $this->ejecutar(
                "DELETE FROM pedidos
                 WHERE id_pedido = :id_pedido
                   AND id_estado_pedido = :cancelado",
                [
                    ':id_pedido' => [$idPedido, PDO::PARAM_INT],
                    ':cancelado' => [self::ESTADO_CANCELADO, PDO::PARAM_INT],
                ]
            );

            $this->db->commit();
            return $stmt->rowCount() === 1;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Devuelve el inventario de un pedido cancelado.
     * Debe ejecutarse dentro de la misma transacción que cambia el estado.
     */
    private function restaurarInventarioPedido(int $idPedido): void
    {
        $items = $this->ejecutar(
            "SELECT id_producto, cantidad
             FROM detalle_pedido
             WHERE id_pedido = :id_pedido
             ORDER BY id_detalle ASC",
            [':id_pedido' => [$idPedido, PDO::PARAM_INT]]
        )->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as $item) {
            $idProducto = (int)$item['id_producto'];
            $cantidad = (int)$item['cantidad'];

            if ($idProducto <= 0 || $cantidad <= 0) {
                throw new RuntimeException('El detalle del pedido contiene datos inválidos.');
            }

            // Bloquea cada producto antes de devolver existencias.
            $producto = $this->ejecutar(
                "SELECT id_producto, stock, id_estado_producto
                 FROM productos
                 WHERE id_producto = :id_producto
                 FOR UPDATE",
                [':id_producto' => [$idProducto, PDO::PARAM_INT]]
            )->fetch(PDO::FETCH_ASSOC);

            if (!$producto) {
                throw new RuntimeException('No fue posible restaurar el inventario del pedido.');
            }

            $this->ejecutar(
                "UPDATE productos
                 SET stock = stock + :cantidad,
                     id_estado_producto = CASE
                         WHEN id_estado_producto = :agotado THEN :disponible
                         ELSE id_estado_producto
                     END
                 WHERE id_producto = :id_producto",
                [
                    ':cantidad'    => [$cantidad, PDO::PARAM_INT],
                    ':agotado'     => [2, PDO::PARAM_INT],
                    ':disponible'  => [1, PDO::PARAM_INT],
                    ':id_producto' => [$idProducto, PDO::PARAM_INT],
                ]
            );
        }
    }

    private function normalizarFiltros(array $filtros): array
    {
        $estado = null;
        if (($filtros['estado'] ?? '') !== '') {
            $estado = filter_var($filtros['estado'], FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1, 'max_range' => 5],
            ]);
            if ($estado === false) {
                throw new InvalidArgumentException('El filtro de estado no es válido.');
            }
        }

        $busqueda = null;
        if (isset($filtros['q']) && is_string($filtros['q'])) {
            $texto = trim($filtros['q']);
            if ($texto !== '') {
                $texto = mb_substr($texto, 0, 100);
                $texto = strtr($texto, ['!' => '!!', '%' => '!%', '_' => '!_']);
                $busqueda = '%' . $texto . '%';
            }
        }

        $desde = $this->normalizarFecha($filtros['desde'] ?? null);
        $hasta = $this->normalizarFecha($filtros['hasta'] ?? null);

        if ($desde !== null && $hasta !== null && $desde > $hasta) {
            throw new InvalidArgumentException('La fecha inicial no puede ser posterior a la fecha final.');
        }

        $orden = is_string($filtros['orden'] ?? null) ? trim($filtros['orden']) : 'recientes';
        if (!isset(self::ORDENES[$orden])) {
            $orden = 'recientes';
        }

        return [
            'estado' => $estado === null ? null : (int)$estado,
            'busqueda' => $busqueda,
            'desde' => $desde,
            'hasta' => $hasta,
            'orden' => $orden,
        ];
    }

    private function normalizarFecha(mixed $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        if (!is_string($valor)) {
            throw new InvalidArgumentException('La fecha no es válida.');
        }

        $fecha = DateTime::createFromFormat('!Y-m-d', $valor);
        if (!$fecha || $fecha->format('Y-m-d') !== $valor) {
            throw new InvalidArgumentException('La fecha no es válida.');
        }

        return $valor;
    }

    private function validarId(int $idPedido): int
    {
        if ($idPedido <= 0) {
            throw new InvalidArgumentException('El ID del pedido no es válido.');
        }
        return $idPedido;
    }

    private function validarEstado(int $estado): int
    {
        if (!array_key_exists($estado, self::TRANSICIONES)) {
            throw new InvalidArgumentException('El estado del pedido no es válido.');
        }
        return $estado;
    }

    private function ejecutar(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->db->prepare($sql);

        foreach ($params as $marcador => [$valor, $tipo]) {
            $stmt->bindValue($marcador, $valor, $tipo);
        }

        $stmt->execute();
        return $stmt;
    }
}
