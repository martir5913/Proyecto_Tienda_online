<?php

 // * Controlador de Pedidos y Checkout
 // * Coordina la transacción ACID de compra y consulta del historial de órdenes.
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

    /**
     * RF11: registra el pedido usando el carrito actual del usuario.
     */
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

            // El carrito se vacía únicamente después de confirmar la transacción.
            $this->carritoCtrl->vaciar();

            // Enviar correo electrónico de confirmación con PHPMailer
            try {
                $pedidoCompleto = $this->pedidoModel->obtenerDetalleCompleto($resultado['id_pedido'], $idUsuario);
                if ($pedidoCompleto) {
                    $this->emailService->enviarConfirmacionPedido($pedidoCompleto);
                }
            } catch (Throwable $eMail) {
                // Si el servidor SMTP tiene problemas, no se interrumpe la confirmación de la orden
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
            // El detalle técnico se registra en el servidor, no se expone al cliente.
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