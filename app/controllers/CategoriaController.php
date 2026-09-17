<?php
 // * Controlador de Categorías
 

namespace App\Controllers;

use App\Models\Categoria;

class CategoriaController
{
    private Categoria $categoriaModel;

    public function __construct()
    {
        $this->categoriaModel = new Categoria();
    }

    public function getTodas(): array
    {
        return $this->categoriaModel->obtenerTodas();
    }
}
