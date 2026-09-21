<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\PedidoAdmin;
use InvalidArgumentException;
use Throwable;

class PedidoAdminController
{
    private PedidoAdmin $model;

    public function __construct()
    {
        $this->model = new PedidoAdmin();
    }

    public function listar(array $filtros = []): array
    {
        try {
            return [
                'success' => true,
                'message' => 'Pedidos recuperados correctamente.',
                'pedidos' => $this->model->obtenerPedidos($filtros),
                'resumen' => $this->model->obtenerResumen(),
                'estados' => $this->model->obtenerEstados(),
            ];
        } catch (InvalidArgumentException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (Throwable $e) {
            error_log('PedidoAdminController::listar - ' . $e->getMessage());
            return ['success' => false, 'message' => 'No fue posible cargar los pedidos.'];
        }
    }

    public function detalle(int $idPedido): array
    {
        try {
            $pedido = $this->model->obtenerDetalle($idPedido);

            if ($pedido === null) {
                return ['success' => false, 'message' => 'El pedido solicitado no existe.'];
            }

            return [
                'success' => true,
                'message' => 'Detalle recuperado correctamente.',
                'pedido' => $pedido,
            ];
        } catch (InvalidArgumentException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (Throwable $e) {
            error_log('PedidoAdminController::detalle - ' . $e->getMessage());
            return ['success' => false, 'message' => 'No fue posible consultar el pedido.'];
        }
    }

    public function cambiarEstado(int $idPedido, int $nuevoEstado): array
    {
        try {
            $resultado = $this->model->cambiarEstado($idPedido, $nuevoEstado);

            return [
                'success' => true,
                'message' => 'El estado del pedido fue actualizado correctamente.',
                'pedido' => $resultado,
            ];
        } catch (InvalidArgumentException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (Throwable $e) {
            error_log('PedidoAdminController::cambiarEstado - ' . $e->getMessage());
            return ['success' => false, 'message' => 'No fue posible actualizar el pedido.'];
        }
    }

    public function eliminarCancelado(int $idPedido): array
    {
        try {
            $eliminado = $this->model->eliminarCancelado($idPedido);

            return [
                'success' => $eliminado,
                'message' => $eliminado
                    ? 'El pedido cancelado fue eliminado definitivamente.'
                    : 'No fue posible eliminar el pedido.',
            ];
        } catch (InvalidArgumentException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (Throwable $e) {
            error_log('PedidoAdminController::eliminarCancelado - ' . $e->getMessage());
            return ['success' => false, 'message' => 'No fue posible eliminar el pedido.'];
        }
    }
}
