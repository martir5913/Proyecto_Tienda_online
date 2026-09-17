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
            "SELECT u.*, r.nombre_rol 
             FROM usuarios u 
             INNER JOIN roles r ON u.id_rol = r.id_rol 
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
            "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.telefono, u.fecha_registro,
                    r.nombre_rol, eu.nombre_estado as estado
             FROM usuarios u
             INNER JOIN roles r ON u.id_rol = r.id_rol
             INNER JOIN estados_usuario eu ON u.id_estado_usuario = eu.id_estado_usuario
             ORDER BY u.id_usuario DESC"
        );
        return $stmt->fetchAll();
    }
}
