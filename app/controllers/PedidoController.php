<?php

 // * Controlador de Pedidos y Checkout
 // * Coordina la transacción ACID de compra y consulta del historial de órdenes.

namespace App\Controllers;

use App\Models\Pedido;
use Exception;

class PedidoController
{
    private Pedido $pedidoModel;
    private CarritoController $carritoCtrl;

    public function __construct()
    {
        $this->pedidoModel = new Pedido();
        $this->carritoCtrl = new CarritoController();
    }

    // * Procesa la orden de compra en una transacción ACID
     
    public function procesarCheckout(int $idUsuario, int $idMetodoPago, string $direccionEnvio, string $notas = ''): array
    {
        $resumen = $this->carritoCtrl->obtenerResumen();
        if (empty($resumen['items'])) {
            return ['success' => false, 'message' => 'No hay artículos en el carrito para procesar.'];
        }

        try {
            $resultado = $this->pedidoModel->crearPedidoTransaccional(
                $idUsuario,
                $idMetodoPago,
                $direccionEnvio,
                $resumen['items'],
                $notas
            );

            // Si la transacción fue exitosa, vaciar el carrito
            $this->carritoCtrl->vaciar();

            return [
                'success'       => true,
                'message'       => 'Pedido registrado exitosamente.',
                'id_pedido'     => $resultado['id_pedido'],
                'numero_pedido' => $resultado['numero_pedido'],
                'total'         => $resultado['total']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al procesar el pedido: ' . $e->getMessage()
            ];
        }
    }

    // * Obtiene el historial de pedidos del usuario autenticado
     
    public function getHistorial(int $idUsuario): array
    {
        return $this->pedidoModel->obtenerPorUsuario($idUsuario);
    }
}
