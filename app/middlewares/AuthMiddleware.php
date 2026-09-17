<?php
 // * Middleware de Autenticación y Autorización por Roles
 

namespace App\Middlewares;

class AuthMiddleware
{
    // * Verifica que el usuario haya iniciado sesión
     
    public static function verificarAutenticado(): void
    {
        if (!estaAutenticado()) {
            if (str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
                jsonResponse(false, "Acceso no autorizado. Debe iniciar sesión.", null, 401);
            } else {
                header('Location: ' . BASE_URL . '/index.php?ruta=login');
                exit;
            }
        }
    }

    // * Verifica que el usuario autenticado posea el rol de Administrador
     
    public static function verificarAdmin(): void
    {
        self::verificarAutenticado();
        if (!esAdmin()) {
            if (str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
                jsonResponse(false, "Permisos insuficientes. Se requiere rol de Administrador.", null, 403);
            } else {
                header('Location: ' . BASE_URL . '/index.php?ruta=home&error=sin_permiso');
                exit;
            }
        }
    }
}
