<?php
 // * Controlador del Carrito de Compras
 // * Gestiona los artículos seleccionados en la sesión del usuario.
 

namespace App\Controllers;

use App\Models\Producto;

if (!class_exists('App\Controllers\CarritoController', false)) {

class CarritoController
{
    private Producto $productoModel;

    public function __construct()
    {
        $this->productoModel = new Producto();
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }
    }

    // * Agrega o incrementa un producto en el carrito
     
    public function agregar(int $idProducto, int $cantidad = 1): array
    {
        $producto = $this->productoModel->obtenerPorId($idProducto);
        if (!$producto) {
            return ['success' => false, 'message' => 'Producto no encontrado.'];
        }

        $stockDisponible = (int)$producto['stock'];
        $cantidadActual = $_SESSION['carrito'][$idProducto]['cantidad'] ?? 0;
        $nuevaCantidad = $cantidadActual + $cantidad;

        if ($nuevaCantidad > $stockDisponible) {
            return ['success' => false, 'message' => "Stock insuficiente. Disponibles: {$stockDisponible} unidades."];
        }

        $_SESSION['carrito'][$idProducto] = [
            'id_producto'     => $idProducto,
            'nombre'          => $producto['nombre'],
            'marca'           => $producto['nombre_marca'],
            'codigo_modelo'   => $producto['codigo_modelo'],
            'precio'          => (float)$producto['precio'],
            'imagen'          => $producto['imagen'],
            'cantidad'        => $nuevaCantidad,
            'subtotal'        => round((float)$producto['precio'] * $nuevaCantidad, 2)
        ];

        return [
            'success'     => true,
            'message'     => 'Producto añadido al carrito.',
            'total_items' => $this->contarItems(),
            'resumen'     => $this->obtenerResumen()
        ];
    }

    // * Actualiza la cantidad de un artículo en el carrito
     
    public function actualizarCantidad(int $idProducto, int $cantidad): array
    {
        if ($cantidad <= 0) {
            return $this->eliminar($idProducto);
        }

        $producto = $this->productoModel->obtenerPorId($idProducto);
        if (!$producto || $cantidad > (int)$producto['stock']) {
            return ['success' => false, 'message' => 'Cantidad solicitada supera el stock disponible.'];
        }

        if (isset($_SESSION['carrito'][$idProducto])) {
            $_SESSION['carrito'][$idProducto]['cantidad'] = $cantidad;
            $_SESSION['carrito'][$idProducto]['subtotal'] = round($_SESSION['carrito'][$idProducto]['precio'] * $cantidad, 2);
        }

        return ['success' => true, 'message' => 'Cantidad actualizada.', 'resumen' => $this->obtenerResumen()];
    }

    // * Elimina un artículo del carrito
     
    public function eliminar(int $idProducto): array
    {
        unset($_SESSION['carrito'][$idProducto]);
        return [
            'success'     => true,
            'message'     => 'Producto eliminado del carrito.',
            'total_items' => $this->contarItems(),
            'resumen'     => $this->obtenerResumen()
        ];
    }

    // * Retorna el número total de unidades en el carrito
     
    public function contarItems(): int
    {
        $count = 0;
        foreach ($_SESSION['carrito'] ?? [] as $item) {
            $count += (int)$item['cantidad'];
        }
        return $count;
    }

    // * Calcula subtotales, IVA y total general del carrito
     
    public function obtenerResumen(): array
    {
        $subtotal = 0.0;
        $items = array_values($_SESSION['carrito'] ?? []);

        foreach ($items as $item) {
            $subtotal += (float)$item['subtotal'];
        }

        $impuesto = round($subtotal * 0.12, 2);
        $total = $subtotal + $impuesto;

        return [
            'items'       => $items,
            'total_items' => $this->contarItems(),
            'subtotal'    => $subtotal,
            'impuesto'    => $impuesto,
            'total'       => $total
        ];
    }

    // * Vacía el carrito de compras
     
    public function vaciar(): void
    {
        $_SESSION['carrito'] = [];
    }
}

}

