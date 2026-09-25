<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Usuario;
use App\Services\EmailService;

class AuthController
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    /**
     * Procesa el inicio de sesión del usuario.
     */
    public function login(string $correo, string $password): array
    {
        $usuario = $this->usuarioModel->buscarPorCorreo($correo);

        if (!$usuario || !password_verify($password, $usuario['password'])) {
            return ['success' => false, 'message' => 'Credenciales inválidas. Verifique correo y contraseña.'];
        }

        $idEstado = (int)($usuario['id_estado_usuario'] ?? 1);
        if ($idEstado !== 1) {
            if ($idEstado === 2) {
                return ['success' => false, 'message' => 'Tu cuenta se encuentra inactiva. Por favor, comunícate con soporte.'];
            }
            if ($idEstado === 3) {
                return ['success' => false, 'message' => 'Tu cuenta ha sido bloqueada por seguridad. Contacta al administrador.'];
            }
            return ['success' => false, 'message' => 'Tu cuenta se encuentra deshabilitada. Contacta al soporte técnico.'];
        }

        $_SESSION['usuario'] = [
            'id_usuario'  => $usuario['id_usuario'],
            'nombre'      => $usuario['nombre'],
            'apellido'    => $usuario['apellido'],
            'correo'      => $usuario['correo'],
            'telefono'    => $usuario['telefono'] ?? '',
            'direccion'   => $usuario['direccion'] ?? '',
            'id_rol'      => (int)$usuario['id_rol'],
            'nombre_rol'  => $usuario['nombre_rol']
        ];

        return ['success' => true, 'message' => 'Sesión iniciada correctamente.', 'usuario' => $_SESSION['usuario']];
    }

    /**
     * Procesa el registro de un nuevo cliente.
     */
    public function registrar(array $datos): array
    {
        $password = $datos['password'] ?? '';
        $passwordConfirm = $datos['password_confirm'] ?? null;

        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'La contraseña debe contener al menos 6 caracteres.'];
        }

        if ($passwordConfirm !== null && $password !== $passwordConfirm) {
            return ['success' => false, 'message' => 'Las contraseñas ingresadas no coinciden.'];
        }

        // Validar si el correo ya existe
        $existente = $this->usuarioModel->buscarPorCorreo($datos['correo'] ?? '');
        if ($existente) {
            return ['success' => false, 'message' => 'El correo electrónico ya se encuentra registrado.'];
        }

        $id = $this->usuarioModel->registrar($datos);
        return ['success' => true, 'message' => 'Usuario registrado exitosamente.', 'id_usuario' => $id];
    }

    // * Actualiza la información del perfil del usuario autenticado
    public function actualizarPerfil(int $idUsuario, array $datos): array
    {
        $nombre = trim($datos['nombre'] ?? '');
        $apellido = trim($datos['apellido'] ?? '');

        if (empty($nombre) || empty($apellido)) {
            return ['success' => false, 'message' => 'El nombre y apellido son obligatorios.'];
        }

        $exito = $this->usuarioModel->actualizarPerfilPropio($idUsuario, [
            'nombre'    => $nombre,
            'apellido'  => $apellido,
            'telefono'  => trim($datos['telefono'] ?? ''),
            'direccion' => trim($datos['direccion'] ?? '')
        ]);

        if ($exito) {
            // Actualizar la sesión actual
            $_SESSION['usuario']['nombre'] = $nombre;
            $_SESSION['usuario']['apellido'] = $apellido;
            $_SESSION['usuario']['telefono'] = trim($datos['telefono'] ?? '');
            $_SESSION['usuario']['direccion'] = trim($datos['direccion'] ?? '');

            return ['success' => true, 'message' => 'Tus datos han sido actualizados correctamente.'];
        }

        return ['success' => false, 'message' => 'No se pudieron actualizar tus datos.'];
    }

    // * Cambia la contraseña del usuario tras validar su clave actual
    public function cambiarPassword(int $idUsuario, string $actual, string $nueva, string $confirm): array
    {
        if (empty($actual) || empty($nueva) || empty($confirm)) {
            return ['success' => false, 'message' => 'Todos los campos de contraseña son requeridos.'];
        }

        if (strlen($nueva) < 6) {
            return ['success' => false, 'message' => 'La nueva contraseña debe tener al menos 6 caracteres.'];
        }

        if ($nueva !== $confirm) {
            return ['success' => false, 'message' => 'La nueva contraseña y su confirmación no coinciden.'];
        }

        $usuario = $this->usuarioModel->obtenerPorId($idUsuario);
        if (!$usuario) {
            return ['success' => false, 'message' => 'Usuario no encontrado.'];
        }

        // Consultar hash actual directamente
        $usuarioCompleto = $this->usuarioModel->buscarPorCorreo($usuario['correo']);
        if (!$usuarioCompleto || !password_verify($actual, $usuarioCompleto['password'])) {
            return ['success' => false, 'message' => 'La contraseña actual ingresada es incorrecta.'];
        }

        $exito = $this->usuarioModel->actualizarPassword($idUsuario, $nueva);
        if ($exito) {
            return ['success' => true, 'message' => 'Tu contraseña ha sido cambiada exitosamente.'];
        }

        return ['success' => false, 'message' => 'Error al actualizar la contraseña.'];
    }

    // * Solicita recuperación de contraseña y envía notificación por correo
    public function solicitarRecuperacion(string $correo): array
    {
        $correo = trim($correo);
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Por favor, ingresa una dirección de correo válida.'];
        }

        $usuario = $this->usuarioModel->buscarPorCorreo($correo);

        // Si el usuario existe y está activo, enviamos el correo
        if ($usuario && (int)($usuario['id_estado_usuario'] ?? 1) === 1) {
            $token = $this->generarTokenRecuperacion($usuario);
            $baseUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost:8000/Proyecto_Tienda_online';
            $enlace = $baseUrl . '/index.php?ruta=restablecer_password&token=' . urlencode($token);

            $emailService = new \App\Services\EmailService();
            $emailService->enviarRecuperacionPassword($usuario, $enlace);
        }

        // Mensaje genérico para proteger la privacidad de las cuentas (evita enumeración de usuarios)
        return [
            'success' => true,
            'message' => 'Si el correo ingresado coincide con una cuenta activa, hemos enviado un enlace para restablecer tu contraseña. Revisa tu bandeja de entrada.'
        ];
    }

    // * Valida un token de recuperación y devuelve el usuario si es válido
    public function validarTokenRecuperacion(string $token): ?array
    {
        $partes = explode('.', base64_decode($token) ?: '');
        if (count($partes) !== 3) {
            return null;
        }

        [$idUsuarioStr, $expiracionStr, $firma] = $partes;
        $idUsuario = (int)$idUsuarioStr;
        $expiracion = (int)$expiracionStr;

        if ($idUsuario <= 0 || $expiracion < time()) {
            return null;
        }

        $usuario = $this->usuarioModel->obtenerPorId($idUsuario);
        if (!$usuario) {
            return null;
        }

        // Consultar hash actual para validar la firma
        $usuarioCompleto = $this->usuarioModel->buscarPorCorreo($usuario['correo']);
        if (!$usuarioCompleto) {
            return null;
        }

        $secret = (string)env('APP_SECRET', 'secret_default_key_2026');
        $firmaEsperada = hash_hmac('sha256', "{$idUsuario}.{$expiracion}." . $usuarioCompleto['password'], $secret);

        if (!hash_equals($firmaEsperada, $firma)) {
            return null;
        }

        return $usuario;
    }

    // * Restablece la contraseña utilizando el token verificado
    public function restablecerPasswordConToken(string $token, string $nuevaPassword, string $confirmPassword): array
    {
        $usuario = $this->validarTokenRecuperacion($token);
        if (!$usuario) {
            return ['success' => false, 'message' => 'El enlace de recuperación es inválido o ha expirado. Solicita uno nuevo.'];
        }

        if (strlen($nuevaPassword) < 6) {
            return ['success' => false, 'message' => 'La nueva contraseña debe tener al menos 6 caracteres.'];
        }

        if ($nuevaPassword !== $confirmPassword) {
            return ['success' => false, 'message' => 'Las contraseñas ingresadas no coinciden.'];
        }

        $exito = $this->usuarioModel->actualizarPassword((int)$usuario['id_usuario'], $nuevaPassword);
        if ($exito) {
            return ['success' => true, 'message' => 'Tu contraseña ha sido restablecida exitosamente. Ya puedes iniciar sesión.'];
        }

        return ['success' => false, 'message' => 'No fue posible actualizar la contraseña. Inténtalo más tarde.'];
    }

    private function generarTokenRecuperacion(array $usuario): string
    {
        $idUsuario = (int)$usuario['id_usuario'];
        $expiracion = time() + 3600; // 1 hora de validez
        $secret = (string)env('APP_SECRET', 'secret_default_key_2026');
        $hashPassword = (string)($usuario['password'] ?? '');

        $firma = hash_hmac('sha256', "{$idUsuario}.{$expiracion}.{$hashPassword}", $secret);
        return base64_encode("{$idUsuario}.{$expiracion}.{$firma}");
    }

    // * Cierra la sesión activa
     
    public function logout(): void
    {
        unset($_SESSION['usuario']);
        session_destroy();
    }
}

