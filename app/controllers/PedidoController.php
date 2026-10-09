<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Pedido;
use App\Services\EmailService;
use DomainException;
use InvalidArgumentException;
use Throwable;

class PedidoController
{
    private Pedido $pedidoModel;
    private CarritoController $carritoCtrl;
    private EmailService $emailService;

    public function __construct()
    {
        $this->pedidoModel = new Pedido();
        $this->carritoCtrl = new CarritoController();
        $this->emailService = new EmailService();
    }

    public function procesarCheckout(
        int $idUsuario,
        int $idMetodoPago,
        string $direccionEnvio,
        string $notas = ''
    ): array {
        $resumen = $this->carritoCtrl->obtenerResumen();

        if (empty($resumen['items'])) {
            return [
                'success' => false,
                'message' => 'No hay artículos en el carrito para procesar.',
            ];
        }

        try {
            $resultado = $this->pedidoModel->crearPedidoTransaccional(
                $idUsuario,
                $idMetodoPago,
                $direccionEnvio,
                $resumen['items'],
                $notas
            );

            $this->carritoCtrl->vaciar();

            try {
                $pedidoCompleto = $this->pedidoModel->obtenerDetalleCompleto($resultado['id_pedido'], $idUsuario);
                if ($pedidoCompleto) {
                    $this->emailService->enviarConfirmacionPedido($pedidoCompleto);
                }
            } catch (Throwable $eMail) {
                error_log('Error en envío de confirmación por correo: ' . $eMail->getMessage());
            }

            return [
                'success' => true,
                'message' => 'Pedido registrado exitosamente.',
                'id_pedido' => $resultado['id_pedido'],
                'numero_pedido' => $resultado['numero_pedido'],
                'subtotal' => $resultado['subtotal'],
                'impuesto' => $resultado['impuesto'],
                'total' => $resultado['total'],
            ];
        } catch (InvalidArgumentException | DomainException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        } catch (Throwable $e) {
            error_log('RF11 checkout: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'No fue posible registrar el pedido. Inténtalo nuevamente.',
            ];
        }
    }

    public function getHistorial(int $idUsuario): array
    {
        return $this->pedidoModel->obtenerPorUsuario($idUsuario);
    }

    public function getDetalle(int $idPedido, int $idUsuario = 0, bool $esAdmin = false): ?array
    {
        return $this->pedidoModel->obtenerDetalleCompleto($idPedido, $idUsuario, $esAdmin);
    }
}