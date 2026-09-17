<?php
 // * Controlador de Productos
 // * Maneja listado de catálogo, búsqueda asíncrona, ficha de producto y CRUD administrativo.
 

namespace App\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;

class ProductoController
{
    private Producto $productoModel;
    private Categoria $categoriaModel;
    private Marca $marcaModel;

    public function __construct()
    {
        $this->productoModel = new Producto();
        $this->categoriaModel = new Categoria();
        $this->marcaModel = new Marca();
    }

    // * Retorna productos destacados para la página de inicio
     
    public function getDestacados(): array
    {
        return $this->productoModel->obtenerDestacados(8);
    }

    // * Retorna listado de catálogo con filtros aplicados
    
    public function getCatalogo(array $filtros = []): array
    {
        return $this->productoModel->obtenerCatalogo($filtros);
    }

    // * Retorna el detalle de un producto específico
     
    public function getDetalle(int $id): ?array
    {
        return $this->productoModel->obtenerPorId($id);
    }
}
