<?php
 // * Modelo de Usuario
 // * Responsable de la persistencia, autenticación y consulta de usuarios y roles.
 

namespace App\Models;

use PDO;

class Usuario extends Model
{
     // * Busca un usuario por su correo electrónico
     
    public function buscarPorCorreo(string $correo): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT u.*, r.nombre_rol, eu.nombre_estado as estado 
             FROM usuarios u 
             INNER JOIN roles r ON u.id_rol = r.id_rol 
             INNER JOIN estados_usuario eu ON u.id_estado_usuario = eu.id_estado_usuario 
             WHERE u.correo = :correo LIMIT 1"
        );
        $stmt->execute([':correo' => $correo]);
        $usuario = $stmt->fetch();
        return $usuario ?: null;
    }

     // * Registra un nuevo usuario cliente con contraseña cifrada en Bcrypt
     
    public function registrar(array $datos): int
    {
        $sql = "INSERT INTO usuarios (id_rol, id_estado_usuario, nombre, apellido, correo, password, telefono, direccion) 
                VALUES (:id_rol, 1, :nombre, :apellido, :correo, :password, :telefono, :direccion)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_rol'    => $datos['id_rol'] ?? 2, // Rol 2: Cliente
            ':nombre'    => $datos['nombre'],
            ':apellido'  => $datos['apellido'],
            ':correo'    => $datos['correo'],
            ':password'  => password_hash($datos['password'], PASSWORD_BCRYPT),
            ':telefono'  => $datos['telefono'] ?? null,
            ':direccion' => $datos['direccion'] ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }

     // * Obtiene el listado completo de usuarios para el panel de administración
     
    public function obtenerTodos(): array
    {
        $stmt = $this->db->query(
            "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.telefono, u.direccion, u.fecha_registro,
                    u.id_rol, u.id_estado_usuario, r.nombre_rol, eu.nombre_estado as estado
             FROM usuarios u
             INNER JOIN roles r ON u.id_rol = r.id_rol
             INNER JOIN estados_usuario eu ON u.id_estado_usuario = eu.id_estado_usuario
             ORDER BY u.id_usuario DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // * Obtiene listado filtrado de usuarios con soporte para búsqueda y orden
    public function obtenerListadoAdmin(array $filtros = []): array
    {
        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.telefono, u.direccion, u.fecha_registro,
                       u.id_rol, u.id_estado_usuario, r.nombre_rol, eu.nombre_estado as estado,
                       (SELECT COUNT(*) FROM pedidos p WHERE p.id_usuario = u.id_usuario) as total_pedidos
                FROM usuarios u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                INNER JOIN estados_usuario eu ON u.id_estado_usuario = eu.id_estado_usuario
                WHERE 1=1";
        
        $params = [];

        if (!empty($filtros['q'])) {
            $sql .= " AND (u.nombre LIKE :q1 OR u.apellido LIKE :q2 OR u.correo LIKE :q3 OR u.telefono LIKE :q4)";
            $qVal = '%' . trim((string)$filtros['q']) . '%';
            $params[':q1'] = $qVal;
            $params[':q2'] = $qVal;
            $params[':q3'] = $qVal;
            $params[':q4'] = $qVal;
        }

        if (!empty($filtros['id_rol'])) {
            $sql .= " AND u.id_rol = :id_rol";
            $params[':id_rol'] = (int)$filtros['id_rol'];
        }

        if (!empty($filtros['id_estado'])) {
            $sql .= " AND u.id_estado_usuario = :id_estado";
            $params[':id_estado'] = (int)$filtros['id_estado'];
        }

        $orden = $filtros['orden'] ?? 'recientes';
        switch ($orden) {
            case 'antiguos':
                $sql .= " ORDER BY u.id_usuario ASC";
                break;
            case 'nombre_asc':
                $sql .= " ORDER BY u.nombre ASC, u.apellido ASC";
                break;
            case 'nombre_desc':
                $sql .= " ORDER BY u.nombre DESC, u.apellido DESC";
                break;
            case 'recientes':
            default:
                $sql .= " ORDER BY u.id_usuario DESC";
                break;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // * Retorna métricas globales de usuarios para los KPIs del panel
    public function obtenerResumenMetricas(): array
    {
        $totalUsuarios = (int)$this->db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        $totalAdmins = (int)$this->db->query("SELECT COUNT(*) FROM usuarios WHERE id_rol = 1")->fetchColumn();
        $totalClientes = (int)$this->db->query("SELECT COUNT(*) FROM usuarios WHERE id_rol = 2")->fetchColumn();
        $totalActivos = (int)$this->db->query("SELECT COUNT(*) FROM usuarios WHERE id_estado_usuario = 1")->fetchColumn();
        $totalInactivos = (int)$this->db->query("SELECT COUNT(*) FROM usuarios WHERE id_estado_usuario = 2")->fetchColumn();
        $totalBloqueados = (int)$this->db->query("SELECT COUNT(*) FROM usuarios WHERE id_estado_usuario = 3")->fetchColumn();

        return [
            'total_usuarios'   => $totalUsuarios,
            'total_admins'     => $totalAdmins,
            'total_clientes'   => $totalClientes,
            'total_activos'    => $totalActivos,
            'total_inactivos'  => $totalInactivos,
            'total_bloqueados' => $totalBloqueados
        ];
    }

    // * Obtiene la información detallada de un usuario por su ID
    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.telefono, u.direccion, u.fecha_registro,
                    u.id_rol, u.id_estado_usuario, r.nombre_rol, eu.nombre_estado as estado,
                    (SELECT COUNT(*) FROM pedidos p WHERE p.id_usuario = u.id_usuario) as total_pedidos,
                    (SELECT COALESCE(SUM(total), 0) FROM pedidos p WHERE p.id_usuario = u.id_usuario AND p.id_estado_pedido != 5) as total_gastado
             FROM usuarios u
             INNER JOIN roles r ON u.id_rol = r.id_rol
             INNER JOIN estados_usuario eu ON u.id_estado_usuario = eu.id_estado_usuario
             WHERE u.id_usuario = :id
             LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        return $usuario ?: null;
    }

    // * Obtiene catálogo de roles
    public function obtenerRoles(): array
    {
        return $this->db->query("SELECT id_rol, nombre_rol, descripcion FROM roles ORDER BY id_rol ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    // * Obtiene catálogo de estados de usuario
    public function obtenerEstados(): array
    {
        return $this->db->query("SELECT id_estado_usuario, nombre_estado FROM estados_usuario ORDER BY id_estado_usuario ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    // * Actualiza la información de un usuario desde el panel de administración
    public function actualizarUsuarioAdmin(int $id, array $datos): bool
    {
        $campos = [
            'nombre'            => $datos['nombre'],
            'apellido'          => $datos['apellido'],
            'telefono'          => $datos['telefono'] ?? null,
            'direccion'         => $datos['direccion'] ?? null,
            'id_rol'            => (int)$datos['id_rol'],
            'id_estado_usuario' => (int)$datos['id_estado_usuario']
        ];

        $sql = "UPDATE usuarios 
                SET nombre = :nombre, 
                    apellido = :apellido, 
                    telefono = :telefono, 
                    direccion = :direccion, 
                    id_rol = :id_rol, 
                    id_estado_usuario = :id_estado_usuario";

        // Si se envió cambio de contraseña
        if (!empty($datos['password'])) {
            $sql .= ", password = :password";
            $campos['password'] = password_hash($datos['password'], PASSWORD_BCRYPT);
        }

        $sql .= " WHERE id_usuario = :id";
        $campos['id'] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($campos);
    }

    // * Cambia el estado de un usuario (Activo = 1, Inactivo = 2, Bloqueado = 3)
    public function cambiarEstado(int $id, int $idEstado): bool
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET id_estado_usuario = :id_estado WHERE id_usuario = :id");
        return $stmt->execute([
            ':id_estado' => $idEstado,
            ':id'        => $id
        ]);
    }

    // * Cambia el rol de un usuario (Admin = 1, Cliente = 2)
    public function cambiarRol(int $id, int $idRol): bool
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET id_rol = :id_rol WHERE id_usuario = :id");
        return $stmt->execute([
            ':id_rol' => $idRol,
            ':id'     => $id
        ]);
    }

    // * Actualiza la información del perfil propio del cliente
    public function actualizarPerfilPropio(int $id, array $datos): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE usuarios 
             SET nombre = :nombre, 
                 apellido = :apellido, 
                 telefono = :telefono, 
                 direccion = :direccion 
             WHERE id_usuario = :id"
        );
        return $stmt->execute([
            ':nombre'    => $datos['nombre'],
            ':apellido'  => $datos['apellido'],
            ':telefono'  => $datos['telefono'] ?? null,
            ':direccion' => $datos['direccion'] ?? null,
            ':id'        => $id
        ]);
    }

    // * Actualiza la contraseña cifrada de un usuario
    public function actualizarPassword(int $id, string $nuevaPassword): bool
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET password = :hash WHERE id_usuario = :id");
        return $stmt->execute([
            ':hash' => password_hash($nuevaPassword, PASSWORD_BCRYPT),
            ':id'   => $id
        ]);
    }
}
