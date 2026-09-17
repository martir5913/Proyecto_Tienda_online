<?php
 // * Controlador de Autenticación
 // * Maneja login, registro y cierre de sesión de usuarios.
 

namespace App\Controllers;

use App\Models\Usuario;

class AuthController
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    // * Procesa el inicio de sesión
    
    public function login(string $correo, string $password): array
    {
        $usuario = $this->usuarioModel->buscarPorCorreo($correo);

        if (!$usuario || !password_verify($password, $usuario['password'])) {
            return ['success' => false, 'message' => 'Credenciales inválidas. Verifique correo y contraseña.'];
        }

        // Guardar datos seguros en sesión
        $_SESSION['usuario'] = [
            'id_usuario'  => $usuario['id_usuario'],
            'nombre'      => $usuario['nombre'],
            'apellido'    => $usuario['apellido'],
            'correo'      => $usuario['correo'],
            'id_rol'      => (int)$usuario['id_rol'],
            'nombre_rol'  => $usuario['nombre_rol']
        ];

        return ['success' => true, 'message' => 'Sesión iniciada correctamente.', 'usuario' => $_SESSION['usuario']];
    }

    // * Procesa el registro de un nuevo cliente
    
    public function registrar(array $datos): array
    {
        // Validar si el correo ya existe
        $existente = $this->usuarioModel->buscarPorCorreo($datos['correo'] ?? '');
        if ($existente) {
            return ['success' => false, 'message' => 'El correo electrónico ya se encuentra registrado.'];
        }

        $id = $this->usuarioModel->registrar($datos);
        return ['success' => true, 'message' => 'Usuario registrado exitosamente.', 'id_usuario' => $id];
    }

    // * Cierra la sesión activa
     
    public function logout(): void
    {
        unset($_SESSION['usuario']);
        session_destroy();
    }
}
