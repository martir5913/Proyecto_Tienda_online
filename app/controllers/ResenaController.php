<?php
 // * Controlador de Reseñas y Calificaciones
 

namespace App\Controllers;

use App\Models\Resena;

class ResenaController
{
    private Resena $resenaModel;

    public function __construct()
    {
        $this->resenaModel = new Resena();
    }

    public function getPorProducto(int $idProducto): array
    {
        return $this->resenaModel->obtenerPorProducto($idProducto);
    }

    public function agregar(int $idUsuario, int $idProducto, int $calificacion, string $comentario): array
    {
        if ($calificacion < 1 || $calificacion > 5) {
            return ['success' => false, 'message' => 'La calificación debe estar entre 1 y 5 estrellas.'];
        }

        $ok = $this->resenaModel->crear($idUsuario, $idProducto, $calificacion, trim($comentario));
        return [
            'success' => $ok,
            'message' => $ok ? 'Reseña publicada con éxito.' : 'No se pudo guardar la reseña.'
        ];
    }
}
