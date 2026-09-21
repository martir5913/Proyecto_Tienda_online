<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Usuario;

class UsuarioAdminController
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    // * Retorna el listado filtrado de usuarios junto con KPIs y catálogos
    public function listar(array $filtros): array
    {
        $usuarios = $this->usuarioModel->obtenerListadoAdmin($filtros);
        $resumen = $this->usuarioModel->obtenerResumenMetricas();
        $roles = $this->usuarioModel->obtenerRoles();
        $estados = $this->usuarioModel->obtenerEstados();

        return [
            'success'  => true,
            'message'  => 'Usuarios obtenidos exitosamente.',
            'usuarios' => $usuarios,
            'resumen'  => $resumen,
            'roles'    => $roles,
            'estados'  => $estados,
        ];
    }

    // * Retorna la información completa de un usuario
    public function detalle(int $id): array
    {
        $usuario = $this->usuarioModel->obtenerPorId($id);
        if (!$usuario) {
            return ['success' => false, 'message' => 'Usuario no encontrado.'];
        }

        return [
            'success' => true,
            'message' => 'Usuario encontrado.',
            'usuario' => $usuario,
        ];
    }

    // * Actualiza la información y permisos de un usuario
    public function actualizar(int $id, array $datos, string $csrfToken): array
    {
        if (!$this->validarCsrf($csrfToken)) {
            return ['success' => false, 'message' => 'Token de seguridad inválido o expirado.'];
        }

        $usuario = $this->usuarioModel->obtenerPorId($id);
        if (!$usuario) {
            return ['success' => false, 'message' => 'El usuario no existe.'];
        }

        $idSesion = (int)($_SESSION['usuario']['id_usuario'] ?? 0);

        // Prevenir autobloqueo o auto-degradación de rol del admin logueado
        if ($id === $idSesion) {
            if (isset($datos['id_estado_usuario']) && (int)$datos['id_estado_usuario'] !== 1) {
                return ['success' => false, 'message' => 'No puedes cambiar el estado de tu propia cuenta activa.'];
            }
            if (isset($datos['id_rol']) && (int)$datos['id_rol'] !== 1) {
                return ['success' => false, 'message' => 'No puedes remover tu propio rol de administrador.'];
            }
        }

        $nombre = trim($datos['nombre'] ?? '');
        $apellido = trim($datos['apellido'] ?? '');

        if (empty($nombre) || empty($apellido)) {
            return ['success' => false, 'message' => 'El nombre y apellido son obligatorios.'];
        }

        // Validar longitud de la contraseña en caso de restablecimiento
        if (isset($datos['password']) && trim((string)$datos['password']) !== '') {
            $passLimpia = trim((string)$datos['password']);
            if (strlen($passLimpia) < 6) {
                return ['success' => false, 'message' => 'La nueva contraseña debe contener al menos 6 caracteres.'];
            }
            $datos['password'] = $passLimpia;
        } else {
            unset($datos['password']);
        }

        $exito = $this->usuarioModel->actualizarUsuarioAdmin($id, $datos);

        if ($exito) {
            return [
                'success' => true,
                'message' => 'Usuario actualizado exitosamente.',
                'usuario' => $this->usuarioModel->obtenerPorId($id),
                'resumen' => $this->usuarioModel->obtenerResumenMetricas()
            ];
        }

        return ['success' => false, 'message' => 'Error al guardar los cambios del usuario.'];
    }

    // * Modifica el estado de cuenta de un usuario
    public function cambiarEstado(int $id, int $idEstado, string $csrfToken): array
    {
        if (!$this->validarCsrf($csrfToken)) {
            return ['success' => false, 'message' => 'Token de seguridad inválido o expirado.'];
        }

        $idSesion = (int)($_SESSION['usuario']['id_usuario'] ?? 0);
        if ($id === $idSesion && $idEstado !== 1) {
            return ['success' => false, 'message' => 'No puedes inactivar o bloquear tu propia cuenta activa.'];
        }

        $exito = $this->usuarioModel->cambiarEstado($id, $idEstado);
        if ($exito) {
            return [
                'success' => true,
                'message' => 'Estado de usuario modificado correctamente.',
                'resumen' => $this->usuarioModel->obtenerResumenMetricas()
            ];
        }

        return ['success' => false, 'message' => 'No se pudo cambiar el estado del usuario.'];
    }

    // * Modifica el rol de un usuario (Admin <-> Cliente)
    public function cambiarRol(int $id, int $idRol, string $csrfToken): array
    {
        if (!$this->validarCsrf($csrfToken)) {
            return ['success' => false, 'message' => 'Token de seguridad inválido o expirado.'];
        }

        $idSesion = (int)($_SESSION['usuario']['id_usuario'] ?? 0);
        if ($id === $idSesion && $idRol !== 1) {
            return ['success' => false, 'message' => 'No puedes degradar tu propio rol de administrador.'];
        }

        $exito = $this->usuarioModel->cambiarRol($id, $idRol);
        if ($exito) {
            return [
                'success' => true,
                'message' => 'Rol de usuario actualizado correctamente.',
                'resumen' => $this->usuarioModel->obtenerResumenMetricas()
            ];
        }

        return ['success' => false, 'message' => 'No se pudo actualizar el rol del usuario.'];
    }

    // * Valida el token CSRF de la sesión administrativa
    private function validarCsrf(string $token): bool
    {
        $sesionToken = $_SESSION['csrf_admin_usuarios'] ?? '';
        return !empty($token) && hash_equals($sesionToken, $token);
    }
}
