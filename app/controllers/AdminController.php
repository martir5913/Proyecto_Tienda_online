<?php

declare(strict_types=1);

namespace App\Controllers;

use Config\Database;
use PDO;

class AdminController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getMetricasDashboard(): array
    {
        $ventasTotales = $this->db->query("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE id_estado_pedido != 5")->fetchColumn();
        $totalPedidos = $this->db->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
        $pedidosPendientes = $this->db->query("SELECT COUNT(*) FROM pedidos WHERE id_estado_pedido = 1")->fetchColumn();
        $totalProductos = $this->db->query("SELECT COUNT(*) FROM productos WHERE id_estado_producto = 1")->fetchColumn();
        $totalClientes = $this->db->query("SELECT COUNT(*) FROM usuarios WHERE id_rol = 2")->fetchColumn();

        return [
            'ventas_totales'     => (float)$ventasTotales,
            'total_pedidos'      => (int)$totalPedidos,
            'pedidos_pendientes' => (int)$pedidosPendientes,
            'total_productos'    => (int)$totalProductos,
            'total_clientes'     => (int)$totalClientes,
        ];
    }
}
