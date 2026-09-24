<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Throwable;

class EmailService
{
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private string $encryption;
    private string $fromAddress;
    private string $fromName;

    public function __construct()
    {
        // Carga de credenciales y configuración SMTP desde variables de entorno .env
        $this->host        = (string)env('MAIL_HOST', 'smtp.gmail.com');
        $this->port        = (int)env('MAIL_PORT', 587);
        $this->username    = (string)env('MAIL_USER', '');
        $this->password    = (string)env('MAIL_PASS', '');
        $this->encryption  = (string)env('MAIL_ENCRYPTION', 'tls');
        $this->fromAddress = (string)env('MAIL_FROM_ADDRESS', $this->username ?: 'no-reply@electrotienda.com');
        $this->fromName    = (string)env('MAIL_FROM_NAME', 'Doméstik - Tienda en Línea');
    }

    /**
     * Envía correo de confirmación de pedido con plantilla HTML detallada.
     *
     * @param array $pedido Detalle completo del pedido (obtenido de Pedido::obtenerDetalleCompleto)
     * @return array ['success' => bool, 'message' => string]
     */
    public function enviarConfirmacionPedido(array $pedido): array
    {
        $destinatarioEmail = trim($pedido['correo_usuario'] ?? $pedido['correo'] ?? '');
        $destinatarioNombre = trim(($pedido['nombre_usuario'] ?? $pedido['nombre'] ?? '') . ' ' . ($pedido['apellido_usuario'] ?? $pedido['apellido'] ?? ''));

        if (!filter_var($destinatarioEmail, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'El correo del cliente no es válido o no está registrado.'
            ];
        }

        // Si no se han configurado credenciales en .env, registrar advertencia y simular éxito
        if (empty($this->username) || $this->username === 'tu_correo@gmail.com') {
            error_log("[EmailService] Correo no enviado: credenciales SMTP pendientes de configurar en .env para el pedido #{$pedido['numero_pedido']}");
            return [
                'success' => true,
                'message' => 'Confirmación generada (simulada por credenciales por defecto en .env).'
            ];
        }

        try {
            $mail = new PHPMailer(true);

            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host       = $this->host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->username;
            $mail->Password   = $this->password;
            $mail->CharSet    = 'UTF-8';

            if ($this->encryption === 'ssl' || $this->port === 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            $mail->Port = $this->port;

            // Destinatarios y Emisor
            $mail->setFrom($this->fromAddress, $this->fromName);
            $mail->addAddress($destinatarioEmail, $destinatarioNombre ?: 'Estimado Cliente');
            $mail->addReplyTo($this->fromAddress, $this->fromName);

            // Contenido del Correo
            $mail->isHTML(true);
            $numeroPedido = $pedido['numero_pedido'] ?? ('#' . ($pedido['id_pedido'] ?? ''));
            $mail->Subject = "Confirmación de Compra - Orden {$numeroPedido} | {$this->fromName}";
            $mail->Body    = $this->obtenerHtmlPlantilla($pedido);
            $mail->AltBody = $this->construirTextoPlano($pedido);

            $mail->send();

            return [
                'success' => true,
                'message' => 'Correo de confirmación enviado exitosamente.'
            ];
        } catch (Exception | Throwable $e) {
            error_log("[EmailService Error] No se pudo enviar el correo para el pedido #{$pedido['id_pedido']}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'El pedido fue procesado, pero ocurrió un error al enviar el correo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Devuelve una orden de muestra para validación y previsualización de plantillas.
     */
    public function obtenerPedidoMuestra(): array
    {
        return [
            'id_pedido'        => 9999,
            'numero_pedido'    => 'PED-' . date('Ymd') . '-DEMO',
            'fecha_pedido'     => date('Y-m-d H:i:s'),
            'nombre_usuario'   => 'Juan Carlos',
            'apellido_usuario' => 'Pérez Morales',
            'correo_usuario'   => 'cliente.demo@gmail.com',
            'telefono_usuario' => '+502 5555-1234',
            'nombre_metodo'    => 'Tarjeta de Crédito / Débito (Visa/Mastercard)',
            'direccion_envio'  => "Avenida Reforma 12-45, Zona 10, Edificio Montúfar, Nivel 4, Oficina 402, Ciudad de Guatemala",
            'notas'            => 'NIT: 1234567-8 | Facturar a: Juan Carlos Pérez Morales - Boleta/Ref: DEMO-2026',
            'subtotal'         => 8500.00,
            'impuesto'         => 1020.00,
            'total'            => 9520.00,
            'items'            => [
                [
                    'id_producto'     => 1,
                    'nombre_producto' => 'Refrigeradora Side by Side French Door 28 Pies',
                    'nombre_marca'    => 'Samsung',
                    'codigo_modelo'   => 'RS27T5200SR',
                    'cantidad'        => 1,
                    'precio_unitario' => 6500.00,
                    'subtotal'        => 6500.00,
                ],
                [
                    'id_producto'     => 2,
                    'nombre_producto' => 'Horno de Microondas Digital 1.4 Pies Inverter',
                    'nombre_marca'    => 'LG',
                    'codigo_modelo'   => 'MS1436GIS',
                    'cantidad'        => 1,
                    'precio_unitario' => 1200.00,
                    'subtotal'        => 1200.00,
                ],
                [
                    'id_producto'     => 3,
                    'nombre_producto' => 'Licuadora Profesional PowerMax 1200W',
                    'nombre_marca'    => 'Oster',
                    'codigo_modelo'   => 'BLSTPEG-G80',
                    'cantidad'        => 2,
                    'precio_unitario' => 400.00,
                    'subtotal'        => 800.00,
                ],
            ]
        ];
    }

    /**
     * Envía un correo de prueba a una dirección específica para validar recepción y diseño.
     */
    public function enviarCorreoPrueba(string $correoDestino, ?array $pedido = null): array
    {
        $correoDestino = trim($correoDestino);
        if (!filter_var($correoDestino, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Por favor, ingresa una dirección de correo válida para la prueba.'
            ];
        }

        $pedidoPrueba = $pedido ?: $this->obtenerPedidoMuestra();
        $pedidoPrueba['correo_usuario'] = $correoDestino;
        $pedidoPrueba['correo'] = $correoDestino;

        return $this->enviarConfirmacionPedido($pedidoPrueba);
    }

    /**
     * Construye y devuelve el HTML de la plantilla del correo con diseño responsivo y formal.
     */
    public function obtenerHtmlPlantilla(array $pedido): string
    {
        $nombreCliente = htmlspecialchars(trim(($pedido['nombre_usuario'] ?? $pedido['nombre'] ?? '') . ' ' . ($pedido['apellido_usuario'] ?? $pedido['apellido'] ?? 'Cliente')));
        $numeroPedido  = htmlspecialchars((string)($pedido['numero_pedido'] ?? ''));
        $fechaPedido   = htmlspecialchars((string)($pedido['fecha_pedido'] ?? date('Y-m-d H:i')));
        $direccion     = htmlspecialchars((string)($pedido['direccion_envio'] ?? 'Dirección no especificada'));
        $metodoPago    = htmlspecialchars((string)($pedido['nombre_metodo'] ?? 'Pago Electrónico'));
        $subtotal      = number_format((float)($pedido['subtotal'] ?? 0), 2);
        $impuesto      = number_format((float)($pedido['impuesto'] ?? 0), 2);
        $total         = number_format((float)($pedido['total'] ?? 0), 2);
        $baseUrl       = defined('BASE_URL') ? BASE_URL : 'http://localhost:8000/Proyecto_Tienda_online';
        $enlaceFactura = $baseUrl . '/index.php?ruta=factura&id=' . ($pedido['id_pedido'] ?? 0);

        $filasProductos = '';
        if (!empty($pedido['items']) && is_array($pedido['items'])) {
            foreach ($pedido['items'] as $item) {
                $nombreProd = htmlspecialchars((string)($item['nombre_producto'] ?? 'Producto'));
                $marcaMod   = htmlspecialchars((string)(($item['nombre_marca'] ?? '') . ' ' . ($item['codigo_modelo'] ?? '')));
                $cant       = (int)($item['cantidad'] ?? 1);
                $pUnit      = number_format((float)($item['precio_unitario'] ?? 0), 2);
                $subt       = number_format((float)($item['subtotal'] ?? 0), 2);

                $filasProductos .= "
                <tr style='border-bottom: 1px solid #e2e8f0;'>
                    <td style='padding: 12px 8px; text-align: left;'>
                        <strong style='color: #1e293b; font-size: 14px; display: block;'>{$nombreProd}</strong>
                        <span style='color: #64748b; font-size: 12px;'>{$marcaMod}</span>
                    </td>
                    <td style='padding: 12px 8px; text-align: center; color: #334155; font-size: 14px;'>{$cant}</td>
                    <td style='padding: 12px 8px; text-align: right; color: #334155; font-size: 14px;'>Q {$pUnit}</td>
                    <td style='padding: 12px 8px; text-align: right; color: #0f172a; font-weight: bold; font-size: 14px;'>Q {$subt}</td>
                </tr>";
            }
        }

        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Confirmación de Pedido</title>
        </head>
        <body style='margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
            <table border='0' cellpadding='0' cellspacing='0' width='100%' style='background-color: #f1f5f9; padding: 30px 10px;'>
                <tr>
                    <td align='center'>
                        <table border='0' cellpadding='0' cellspacing='0' width='100%' style='max-width: 620px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.08);'>
                            
                            <!-- Header de la marca -->
                            <tr>
                                <td style='background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); padding: 30px 24px; text-align: center;'>
                                    <h1 style='color: #ffffff; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: 0.5px;'>
                                        {$this->fromName}
                                    </h1>
                                    <p style='color: #e0e7ff; margin: 8px 0 0 0; font-size: 14px;'>
                                        ¡Gracias por tu compra! Tu pedido ha sido confirmado.
                                    </p>
                                </td>
                            </tr>

                            <!-- Tarjeta de Contenido -->
                            <tr>
                                <td style='padding: 28px 24px;'>
                                    <p style='font-size: 16px; color: #1e293b; margin: 0 0 16px 0;'>
                                        Hola <strong>{$nombreCliente}</strong>,
                                    </p>
                                    <p style='font-size: 14px; color: #475569; line-height: 1.6; margin: 0 0 20px 0;'>
                                        Hemos recibido tu orden y ya nos encontramos preparando el despacho de tus productos. A continuación encontrarás el resumen detallado de tu compra:
                                    </p>

                                    <!-- Ficha de Datos del Pedido -->
                                    <table border='0' cellpadding='0' cellspacing='0' width='100%' style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 24px;'>
                                        <tr>
                                            <td width='50%' style='padding: 6px 8px; vertical-align: top;'>
                                                <span style='font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; display: block;'>No. de Pedido</span>
                                                <strong style='font-size: 14px; color: #0d6efd;'>{$numeroPedido}</strong>
                                            </td>
                                            <td width='50%' style='padding: 6px 8px; vertical-align: top;'>
                                                <span style='font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; display: block;'>Fecha de Compra</span>
                                                <strong style='font-size: 13px; color: #334155;'>{$fechaPedido}</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width='50%' style='padding: 6px 8px; vertical-align: top;'>
                                                <span style='font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; display: block;'>Método de Pago</span>
                                                <strong style='font-size: 13px; color: #334155;'>{$metodoPago}</strong>
                                            </td>
                                            <td width='50%' style='padding: 6px 8px; vertical-align: top;'>
                                                <span style='font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; display: block;'>Dirección de Entrega</span>
                                                <span style='font-size: 13px; color: #334155;'>{$direccion}</span>
                                            </td>
                                        </tr>
                                    </table>

                                    <!-- Tabla de Productos -->
                                    <h3 style='font-size: 15px; color: #0f172a; margin: 0 0 12px 0; border-bottom: 2px solid #0d6efd; padding-bottom: 6px; display: inline-block;'>
                                        Detalle de Productos
                                    </h3>
                                    <table border='0' cellpadding='0' cellspacing='0' width='100%' style='border-collapse: collapse; margin-bottom: 20px;'>
                                        <thead>
                                            <tr style='background-color: #f1f5f9; border-bottom: 2px solid #cbd5e1;'>
                                                <th style='padding: 10px 8px; text-align: left; font-size: 12px; color: #475569; text-transform: uppercase;'>Producto</th>
                                                <th style='padding: 10px 8px; text-align: center; font-size: 12px; color: #475569; text-transform: uppercase;'>Cant.</th>
                                                <th style='padding: 10px 8px; text-align: right; font-size: 12px; color: #475569; text-transform: uppercase;'>P. Unit.</th>
                                                <th style='padding: 10px 8px; text-align: right; font-size: 12px; color: #475569; text-transform: uppercase;'>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {$filasProductos}
                                        </tbody>
                                    </table>

                                    <!-- Totales Financieros -->
                                    <table border='0' cellpadding='0' cellspacing='0' width='100%' style='margin-bottom: 26px;'>
                                        <tr>
                                            <td width='55%'></td>
                                            <td width='45%'>
                                                <table border='0' cellpadding='4' cellspacing='0' width='100%'>
                                                    <tr>
                                                        <td style='font-size: 13px; color: #64748b; text-align: right;'>Subtotal:</td>
                                                        <td style='font-size: 13px; color: #1e293b; font-weight: 600; text-align: right;'>Q {$subtotal}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style='font-size: 13px; color: #64748b; text-align: right;'>IVA (12%):</td>
                                                        <td style='font-size: 13px; color: #1e293b; font-weight: 600; text-align: right;'>Q {$impuesto}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style='font-size: 13px; color: #64748b; text-align: right;'>Envío Especializado:</td>
                                                        <td style='font-size: 13px; color: #16a34a; font-weight: 600; text-align: right;'>Gratis</td>
                                                    </tr>
                                                    <tr style='border-top: 2px solid #0f172a;'>
                                                        <td style='font-size: 15px; color: #0f172a; font-weight: bold; text-align: right; padding-top: 8px;'>Total Pagado:</td>
                                                        <td style='font-size: 16px; color: #0d6efd; font-weight: 800; text-align: right; padding-top: 8px;'>Q {$total}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>

                                    <!-- Botón de Acción -->
                                    <div style='text-align: center; margin: 30px 0 10px 0;'>
                                        <a href='{$enlaceFactura}' target='_blank' style='background-color: #0d6efd; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-weight: bold; font-size: 14px; display: inline-block; box-shadow: 0 2px 4px rgba(13,110,253,0.3);'>
                                            Ver Factura Digital en Línea
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td style='background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 24px; text-align: center;'>
                                    <p style='color: #64748b; font-size: 12px; margin: 0 0 6px 0;'>
                                        Este es un correo automático generado por el sistema de <strong>{$this->fromName}</strong>.
                                    </p>
                                    <p style='color: #94a3b8; font-size: 11px; margin: 0;'>
                                        © " . date('Y') . " Todos los derechos reservados.
                                    </p>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";
    }

    /**
     * Alternativa en texto plano para clientes de correo sin soporte HTML.
     */
    private function construirTextoPlano(array $pedido): string
    {
        $nombreCliente = trim(($pedido['nombre_usuario'] ?? '') . ' ' . ($pedido['apellido_usuario'] ?? 'Cliente'));
        $numeroPedido  = $pedido['numero_pedido'] ?? '';
        $total         = number_format((float)($pedido['total'] ?? 0), 2);

        $texto = "CONFIRMACIÓN DE COMPRA\n";
        $texto .= "======================\n\n";
        $texto .= "Hola {$nombreCliente},\n\n";
        $texto .= "Hemos recibido tu pedido #{$numeroPedido} exitosamente.\n\n";
        $texto .= "Total: Q {$total}\n";
        $texto .= "Método de Pago: " . ($pedido['nombre_metodo'] ?? '') . "\n";
        $texto .= "Dirección de Entrega: " . ($pedido['direccion_envio'] ?? '') . "\n\n";
        $texto .= "Gracias por tu compra en {$this->fromName}.\n";

        return $texto;
    }
}
