<?php
/**
 * Controlador de Wishlist (Lista de Deseos)
 */

namespace App\Controllers;

use App\Models\Wishlist;

class WishlistController
{
    private Wishlist $wishlistModel;

    public function __construct()
    {
        $this->wishlistModel = new Wishlist();
    }

    public function getFavoritos(int $idUsuario): array
    {
        return $this->wishlistModel->obtenerPorUsuario($idUsuario);
    }

    public function agregar(int $idUsuario, int $idProducto): array
    {
        $ok = $this->wishlistModel->agregar($idUsuario, $idProducto);
        return [
            'success' => $ok,
            'message' => $ok ? 'Producto añadido a tu lista de deseos.' : 'No se pudo añadir a la lista.'
        ];
    }

    public function eliminar(int $idUsuario, int $idProducto): array
    {
        $ok = $this->wishlistModel->eliminar($idUsuario, $idProducto);
        return [
            'success' => $ok,
            'message' => $ok ? 'Producto eliminado de la lista de deseos.' : 'No se pudo eliminar.'
        ];
    }
}
