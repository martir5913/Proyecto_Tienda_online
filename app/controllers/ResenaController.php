<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Resena;
use PDOException;
use Throwable;

class ResenaController
{
    private const COMENTARIO_MAXIMO = 1000;

    private Resena $resenaModel;

    public function __construct()
    {
        $this->resenaModel = new Resena();
    }

    public function getPorProducto(int $idProducto): array
    {
        if ($idProducto <= 0) {
            return [
                'success' => false,
                'message' => 'Producto inválido.',
                'status' => 422,
            ];
        }

        return [
            'success' => true,
            'message' => 'Reseñas obtenidas correctamente.',
            'status' => 200,
            'data' => [
                'resumen' => $this->resenaModel->obtenerResumenProducto($idProducto),
                'resenas' => $this->resenaModel->obtenerPorProducto($idProducto),
            ],
        ];
    }

   public function getEstadoUsuario(int $idUsuario, int $idProducto): array
    {
        if ($idUsuario <= 0 || $idProducto <= 0) {
            return [
                'success' => false,
                'message' => 'Datos inválidos.',
                'status' => 422,
            ];
        }

        $resena =
            $this->resenaModel->obtenerDelUsuario(
                $idUsuario,
                $idProducto
            );

        return [
            'success' => true,
            'message' => 'Estado de reseña obtenido.',
            'status' => 200,
            'data' => [
                'puede_resenar' => $resena === null,
                'ya_resenado' => $resena !== null,
                'resena' => $resena,
            ],
        ];
    }

    public function agregar(
        int $idUsuario,
        int $idProducto,
        int $calificacion,
        string $comentario
    ): array {
        if ($idUsuario <= 0 || $idProducto <= 0) {
            return [
                'success' => false,
                'message' => 'Los datos de la reseña no son válidos.',
                'status' => 422,
            ];
        }

        if ($calificacion < 1 || $calificacion > 5) {
            return [
                'success' => false,
                'message' => 'Selecciona una calificación entre 1 y 5 estrellas.',
                'status' => 422,
            ];
        }

        $comentario = trim($comentario);

        if ($comentario === '') {
            return [
                'success' => false,
                'message' => 'Escribe un comentario sobre el producto.',
                'status' => 422,
            ];
        }

        if (mb_strlen($comentario) > self::COMENTARIO_MAXIMO) {
            return [
                'success' => false,
                'message' => 'El comentario no puede superar los 1000 caracteres.',
                'status' => 422,
            ];
        }

        if ($this->resenaModel->obtenerDelUsuario($idUsuario, $idProducto) !== null) {
            return [
                'success' => false,
                'message' => 'Ya registraste una reseña para este producto.',
                'status' => 409,
            ];
        }

        try {
            $idResena = $this->resenaModel->crear(
                $idUsuario,
                $idProducto,
                $calificacion,
                $comentario
            );

            if ($idResena <= 0) {
                throw new \RuntimeException('No se generó el identificador de la reseña.');
            }

            return [
                'success' => true,
                'message' => 'Reseña publicada correctamente.',
                'status' => 201,
                'data' => [
                    'id_resena' => $idResena,
                    'id_producto' => $idProducto,
                    'calificacion' => $calificacion,
                ],
            ];
        } catch (PDOException $e) {
            // 23000 cubre violaciones de integridad como el UNIQUE usuario/producto.
            if ((string)$e->getCode() === '23000') {
                return [
                    'success' => false,
                    'message' => 'Ya registraste una reseña para este producto.',
                    'status' => 409,
                ];
            }

            error_log('RF14 reseña PDO: ' . $e->getMessage());
        } catch (Throwable $e) {
            error_log('RF14 reseña: ' . $e->getMessage());
        }

        return [
            'success' => false,
            'message' => 'No fue posible guardar la reseña. Inténtalo nuevamente.',
            'status' => 500,
        ];
    }
}