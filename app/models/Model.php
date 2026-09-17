<?php
/**
 * Clase Base Model
 * Proporciona acceso a la instancia PDO compartida y métodos comunes de consulta.
 */

namespace App\Models;

use Config\Database;
use PDO;

abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }
}
