<?php

declare(strict_types=1);

$tituloPagina = "Factura Electrónica | Doméstik";

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/middlewares/AuthMiddleware.php';
require_once dirname(__DIR__) . '/app/controllers/PedidoController.php';

use App\Middlewares\AuthMiddleware;
use App\Controllers\PedidoController;

AuthMiddleware::verificarAutenticado();

$idUsuario = (int)$_SESSION['usuario']['id_usuario'];
$idPedido = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

if ($idPedido <= 0) {
    header('Location: ' . BASE_URL . '/index.php?ruta=mis_pedidos');
    exit;
}

$pedidoCtrl = new PedidoController();
$pedido = $pedidoCtrl->getDetalle((int)$idPedido, $idUsuario, esAdmin());

if (!$pedido) {
    die("Pedido no encontrado o no tiene permisos para visualizar esta factura.");
}

// Extraer NIT y Nombre de facturación de las notas si existen
$nitCliente = 'C/F';
$nombreFactura = $pedido['nombre_usuario'] . ' ' . $pedido['apellido_usuario'];

if (!empty($pedido['notas'])) {
    if (preg_match('/NIT:\s*([^\|]+)/i', $pedido['notas'], $mNit)) {
        $nitCliente = trim($mNit[1]);
    }
    if (preg_match('/Facturar a:\s*([^\|-]+)/i', $pedido['notas'], $mNom)) {
        $nombreFactura = trim($mNom[1]);
    }
}

// Generador de UUID / Autorización fiscal determinista basado en el ID
$uuidFiscal = strtoupper(md5('domestik_fel_' . $pedido['id_pedido'] . '_' . $pedido['numero_pedido']));
$uuidFormateado = substr($uuidFiscal, 0, 8) . '-' . substr($uuidFiscal, 8, 4) . '-' . substr($uuidFiscal, 12, 4) . '-' . substr($uuidFiscal, 16, 4) . '-' . substr($uuidFiscal, 20, 12);
$dteNumero = str_pad((string)$pedido['id_pedido'], 8, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura <?= htmlspecialchars($pedido['numero_pedido']) ?> - Doméstik</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            color: #1e293b;
        }
        .factura-card {
            max-width: 850px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
            padding: 40px;
        }
        .factura-header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .empresa-logo {
            font-size: 1.8rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
        }
        .dte-badge {
            border: 2px solid #0f172a;
            border-radius: 8px;
            padding: 12px 18px;
            text-align: center;
            background: #f8fafc;
        }
        .table-items thead th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 0.85rem;
            text-transform: uppercase;
            font-weight: 600;
            padding: 10px 12px;
        }
        .table-items tbody td {
            font-size: 0.9rem;
            padding: 10px 12px;
        }
        .firma-sat {
            border-top: 1px dashed #cbd5e1;
            padding-top: 15px;
            font-size: 0.75rem;
            color: #64748b;
        }
        @media print {
            body {
                background: #ffffff;
            }
            .factura-card {
                box-shadow: none;
                margin: 0;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="container py-4">
    <!-- Barra superior de botones de acción -->
    <div class="d-flex justify-content-between align-items-center max-w-850 mx-auto mb-3 no-print" style="max-width: 850px;">
        <a href="<?= BASE_URL ?>/index.php?ruta=mis_pedidos" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Volver a Mis Pedidos
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm px-3">
                <i class="bi bi-printer-fill me-1"></i> Imprimir / Guardar en PDF
            </button>
        </div>
    </div>

    <!-- Hoja de Factura Electrónica -->
    <div class="factura-card">
        <!-- Cabecera -->
        <div class="row align-items-center factura-header">
            <div class="col-7">
                <div class="empresa-logo d-flex align-items-center mb-1">
                    <i class="bi bi-lightning-charge-fill text-warning me-2"></i> Doméstik, S.A.
                </div>
                <p class="small text-muted mb-0">Comercio Electrónico de Electrodomésticos y Línea Blanca</p>
                <p class="small text-muted mb-0"><strong>NIT Emisor:</strong> 10293847-5</p>
                <p class="small text-muted mb-0">Ciudad de Guatemala, Guatemala · PBX: (502) 2200-0000</p>
                <p class="small text-muted mb-0">soporte@domestik.com</p>
            </div>
            <div class="col-5">
                <div class="dte-badge">
                    <h6 class="fw-bold mb-1 text-dark">FACTURA ELECTRÓNICA</h6>
                    <div class="small fw-semibold text-primary">DOCUMENTO TRIBUTARIO ELECTRÓNICO (DTE)</div>
                    <div class="small text-dark mt-1"><strong>SERIE:</strong> FEL-DOM-2026</div>
                    <div class="small text-dark"><strong>NO. DTE:</strong> <?= $dteNumero ?></div>
                </div>
            </div>
        </div>

        <!-- Información Fiscal y Autorización SAT -->
        <div class="bg-light p-3 rounded-3 mb-4 border">
            <div class="row g-2 small">
                <div class="col-md-7">
                    <span class="text-muted d-block">Número de Autorización (UUID):</span>
                    <span class="font-monospace fw-bold text-dark"><?= $uuidFormateado ?></span>
                </div>
                <div class="col-md-5 text-md-end">
                    <span class="text-muted d-block">Fecha y Hora de Emisión / Certificación:</span>
                    <strong><?= date('d/m/Y H:i:s', strtotime($pedido['fecha_pedido'])) ?></strong>
                </div>
            </div>
        </div>

        <!-- Datos del Receptor (Cliente) -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="p-3 border rounded-3 h-100 bg-white">
                    <h6 class="fw-bold text-primary small mb-2"><i class="bi bi-person-badge me-1"></i>DATOS DEL CLIENTE (RECEPTOR)</h6>
                    <p class="small mb-1"><strong>Nombre / Razón Social:</strong> <?= htmlspecialchars($nombreFactura) ?></p>
                    <p class="small mb-1"><strong>NIT Receptor:</strong> <?= htmlspecialchars($nitCliente) ?></p>
                    <p class="small mb-0"><strong>Correo:</strong> <?= htmlspecialchars($pedido['correo_usuario']) ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 border rounded-3 h-100 bg-white">
                    <h6 class="fw-bold text-primary small mb-2"><i class="bi bi-truck me-1"></i>INFORMACIÓN DEL PEDIDO Y ENTREGA</h6>
                    <p class="small mb-1"><strong>No. Pedido Web:</strong> <?= htmlspecialchars($pedido['numero_pedido']) ?></p>
                    <p class="small mb-1"><strong>Método de Pago:</strong> <?= htmlspecialchars($pedido['nombre_metodo']) ?></p>
                    <p class="small mb-0"><strong>Dirección:</strong> <?= htmlspecialchars($pedido['direccion_envio']) ?></p>
                </div>
            </div>
        </div>

        <!-- Tabla de Artículos -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-items align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 60px;">Cant.</th>
                        <th>Descripción del Producto</th>
                        <th class="text-center">Modelo / Marca</th>
                        <th class="text-end" style="width: 120px;">Precio Unit.</th>
                        <th class="text-end" style="width: 130px;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedido['items'] as $it): ?>
                        <tr>
                            <td class="text-center fw-bold"><?= (int)$it['cantidad'] ?></td>
                            <td>
                                <strong class="text-dark"><?= htmlspecialchars($it['nombre_producto']) ?></strong>
                                <small class="text-muted d-block">Cat: <?= htmlspecialchars($it['nombre_categoria']) ?></small>
                            </td>
                            <td class="text-center small"><?= htmlspecialchars($it['nombre_marca']) ?> (<?= htmlspecialchars($it['codigo_modelo']) ?>)</td>
                            <td class="text-end">Q <?= number_format((float)$it['precio_unitario'], 2) ?></td>
                            <td class="text-end fw-bold">Q <?= number_format((float)$it['subtotal'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Totales e Impuestos -->
        <div class="row g-4 mb-4">
            <div class="col-md-7">
                <div class="p-3 bg-light rounded-3 border h-100 small">
                    <h6 class="fw-bold mb-2">Régimen Fiscal</h6>
                    <p class="mb-1 text-muted">Sujeto a Pagos Trimestrales ISR · Régimen General de IVA (12%).</p>
                    <p class="mb-0 text-muted">Garantía oficial Doméstik respaldada por factura electrónica.</p>
                </div>
            </div>
            <div class="col-md-5">
                <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-0 small">
                        <tr>
                            <td class="text-muted">Subtotal sin IVA:</td>
                            <td class="text-end fw-semibold">Q <?= number_format((float)$pedido['subtotal'], 2) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">IVA (12% Crédito Fiscal):</td>
                            <td class="text-end fw-semibold">Q <?= number_format((float)$pedido['impuesto'], 2) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Cargos por Envío:</td>
                            <td class="text-end text-success fw-semibold">Q 0.00</td>
                        </tr>
                        <tr class="border-top border-dark fs-6 fw-bold">
                            <td class="pt-2 text-dark">TOTAL FACTURADO:</td>
                            <td class="pt-2 text-end text-primary">Q <?= number_format((float)$pedido['total'], 2) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pie de Factura y Certificación Electrónica -->
        <div class="firma-sat row align-items-center">
            <div class="col-md-9">
                <p class="mb-1"><strong>Certificador DTE:</strong> INFILE, S.A. · NIT Certificador: 1254829-1</p>
                <p class="mb-0">Documento electrónico emitido conforme al Decreto 27-92 del Congreso de la República de Guatemala. Esta es una representación gráfica de un Documento Tributario Electrónico (DTE).</p>
            </div>
            <div class="col-md-3 text-center text-md-end mt-2 mt-md-0">
                <div class="d-inline-block p-2 border rounded bg-light text-center">
                    <i class="bi bi-qr-code fs-1 text-dark"></i>
                    <span class="d-block extra-small text-muted" style="font-size: 9px;">Validación SAT</span>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
