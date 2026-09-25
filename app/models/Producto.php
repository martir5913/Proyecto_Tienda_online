<?php

declare(strict_types=1);

// Modelo de Producto (Electrodomésticos y Línea Blanca)
// Maneja catálogo, filtros y operaciones CRUD administrativas.
namespace App\Models;

use InvalidArgumentException;
use PDO;

class Producto extends Model
{
    // Estados del producto: evita "números mágicos" regados por el código.
    public const ESTADO_DISPONIBLE     = 1;
    public const ESTADO_AGOTADO        = 2;
    public const ESTADO_DESCONTINUADO  = 3;

    // Límites defensivos para la paginación.
    private const LIMITE_MINIMO  = 1;
    private const LIMITE_MAXIMO  = 100;
    private const LIMITE_DEFECTO = 24;

    // Columnas que el catálogo público puede exponer.
    private const COLUMNAS_PUBLICAS = 'p.id_producto, p.id_categoria, p.id_marca,
                                       p.id_estado_producto, p.codigo_modelo, p.nombre,
                                       p.descripcion, p.especificaciones, p.precio,
                                       p.stock, p.imagen, p.destacado';
    // Lista blanca de ordenamientos. La clave viene del usuario, el SQL no.
    private const ORDENES_PERMITIDOS = [
        'recientes'   => 'p.id_producto DESC',
        'precio_asc'  => 'p.precio ASC, p.id_producto DESC',
        'precio_desc' => 'p.precio DESC, p.id_producto DESC',
        'nombre'      => 'p.nombre ASC',
        'mejor'       => 'promedio_calificacion DESC, total_resenas DESC',
    ];
    // Obtiene el listado público de productos con filtros dinámicos.
    public function obtenerCatalogo(array $filtros = []): array
    {
        $sql = 'SELECT ' . self::COLUMNAS_PUBLICAS . ',
                       c.nombre_categoria,
                       m.nombre_marca,
                       ep.nombre_estado AS estado_nombre,
                       COALESCE(AVG(r.calificacion), 0) AS promedio_calificacion,
                       COUNT(r.id_resena) AS total_resenas
                FROM productos p
                INNER JOIN categorias c ON p.id_categoria = c.id_categoria
                INNER JOIN marcas m ON p.id_marca = m.id_marca
                INNER JOIN estados_producto ep ON p.id_estado_producto = ep.id_estado_producto
                LEFT JOIN resenas r ON p.id_producto = r.id_producto
                WHERE p.id_estado_producto IN (:estado_disponible, :estado_agotado)';

        // Cada entrada: [marcador => [valor, tipo PDO]]
        $params = [
            ':estado_disponible' => [
                self::ESTADO_DISPONIBLE,
                PDO::PARAM_INT
            ],
            ':estado_agotado' => [
                self::ESTADO_AGOTADO,
                PDO::PARAM_INT
            ],
        ];

        $categoria = $this->idPositivoONulo($filtros['categoria'] ?? null);
        if ($categoria !== null) {
            $sql .= ' AND p.id_categoria = :categoria';
            $params[':categoria'] = [$categoria, PDO::PARAM_INT];
        }

        $marca = $this->idPositivoONulo($filtros['marca'] ?? null);
        if ($marca !== null) {
            $sql .= ' AND p.id_marca = :marca';
            $params[':marca'] = [$marca, PDO::PARAM_INT];
        }

      $busqueda = $this->normalizarBusqueda($filtros['busqueda'] ?? null);

        if ($busqueda !== null) {
            $sql .= " AND (
                p.nombre LIKE :busqueda_nombre ESCAPE '!'
                OR p.descripcion LIKE :busqueda_descripcion ESCAPE '!'
                OR p.codigo_modelo LIKE :busqueda_modelo ESCAPE '!'
                OR m.nombre_marca LIKE :busqueda_marca ESCAPE '!'
            )";

            $params[':busqueda_nombre'] = [$busqueda, PDO::PARAM_STR];
            $params[':busqueda_descripcion'] = [$busqueda, PDO::PARAM_STR];
            $params[':busqueda_modelo'] = [$busqueda, PDO::PARAM_STR];
            $params[':busqueda_marca'] = [$busqueda, PDO::PARAM_STR];
        }

        $precioMin = $this->decimalONulo($filtros['precio_min'] ?? null);
        if ($precioMin !== null) {
            $sql .= ' AND p.precio >= :precio_min';
            $params[':precio_min'] = [$precioMin, PDO::PARAM_STR]; // DECIMAL: string evita pérdida de precisión
        }

        $precioMax = $this->decimalONulo($filtros['precio_max'] ?? null);
        if ($precioMax !== null) {
            $sql .= ' AND p.precio <= :precio_max';
            $params[':precio_max'] = [$precioMax, PDO::PARAM_STR];
        }

        // GROUP BY obligatorio: hay agregados (AVG/COUNT) junto a columnas normales.
        // Sin esto, con ONLY_FULL_GROUP_BY activo MariaDB lanza error.
        $sql .= ' GROUP BY ' . self::COLUMNAS_PUBLICAS . ',
                              c.nombre_categoria, m.nombre_marca, ep.nombre_estado';

        // ORDER BY no admite placeholders: se resuelve contra la lista blanca.
        $sql .= ' ORDER BY ' . $this->ordenSeguro($filtros['orden'] ?? null);

        // LIMIT/OFFSET sí admiten placeholders, pero solo con enteros reales.
        [$limite, $offset] = $this->paginacion($filtros);
        $sql .= ' LIMIT :limite OFFSET :offset';
        $params[':limite'] = [$limite, PDO::PARAM_INT];
        $params[':offset'] = [$offset, PDO::PARAM_INT];

        return $this->ejecutar($sql, $params)->fetchAll();
    }
    // Cuenta el total de resultados del catálogo (para paginar sin traer todo).
    public function contarCatalogo(array $filtros = []): int
    {
        $sql = 'SELECT COUNT(DISTINCT p.id_producto)
                FROM productos p
                INNER JOIN categorias c ON p.id_categoria = c.id_categoria
                INNER JOIN marcas m ON p.id_marca = m.id_marca
                WHERE p.id_estado_producto IN (:estado_disponible, :estado_agotado)';

        $params = [
            ':estado_disponible' => [
                self::ESTADO_DISPONIBLE,
                PDO::PARAM_INT
            ],
            ':estado_agotado' => [
                self::ESTADO_AGOTADO,
                PDO::PARAM_INT
            ],
        ];

        $categoria = $this->idPositivoONulo($filtros['categoria'] ?? null);
        if ($categoria !== null) {
            $sql .= ' AND p.id_categoria = :categoria';
            $params[':categoria'] = [$categoria, PDO::PARAM_INT];
        }

        $marca = $this->idPositivoONulo($filtros['marca'] ?? null);
        if ($marca !== null) {
            $sql .= ' AND p.id_marca = :marca';
            $params[':marca'] = [$marca, PDO::PARAM_INT];
        }

       $busqueda = $this->normalizarBusqueda($filtros['busqueda'] ?? null);
        if ($busqueda !== null) {
            $sql .= " AND (
                p.nombre LIKE :busqueda_nombre ESCAPE '!'
                OR p.descripcion LIKE :busqueda_descripcion ESCAPE '!'
                OR p.codigo_modelo LIKE :busqueda_modelo ESCAPE '!'
                OR m.nombre_marca LIKE :busqueda_marca ESCAPE '!'
            )";

            $params[':busqueda_nombre'] = [$busqueda, PDO::PARAM_STR];
            $params[':busqueda_descripcion'] = [$busqueda, PDO::PARAM_STR];
            $params[':busqueda_modelo'] = [$busqueda, PDO::PARAM_STR];
            $params[':busqueda_marca'] = [$busqueda, PDO::PARAM_STR];
        }
    }

    // Obtiene un producto público por ID (solo si está disponible).
    public function obtenerPublicoPorId(int $idProducto): ?array
    {
        if ($idProducto <= 0) {
            return null;
        }

        $sql = 'SELECT ' . self::COLUMNAS_PUBLICAS . ',
                       c.nombre_categoria, m.nombre_marca, ep.nombre_estado AS estado_nombre
                FROM productos p
                INNER JOIN categorias c ON p.id_categoria = c.id_categoria
                INNER JOIN marcas m ON p.id_marca = m.id_marca
                INNER JOIN estados_producto ep ON p.id_estado_producto = ep.id_estado_producto
                WHERE p.id_producto = :id
                  AND p.id_estado_producto IN (:estado_disponible, :estado_agotado)
                LIMIT 1';

       $producto = $this->ejecutar($sql, [
            ':id' => [
                $idProducto,
                PDO::PARAM_INT
            ],
            ':estado_disponible' => [
                self::ESTADO_DISPONIBLE,
                PDO::PARAM_INT
            ],
            ':estado_agotado' => [
                self::ESTADO_AGOTADO,
                PDO::PARAM_INT
            ],
        ])->fetch();

        return $producto ?: null;
    }

    // Obtiene productos destacados para la portada.
    public function obtenerDestacados(int $limite = 6): array
    {
        $limite = $this->acotarLimite($limite);

        $sql = 'SELECT ' . self::COLUMNAS_PUBLICAS . ',
                       c.nombre_categoria, m.nombre_marca
                FROM productos p
                INNER JOIN categorias c ON p.id_categoria = c.id_categoria
                INNER JOIN marcas m ON p.id_marca = m.id_marca
                WHERE p.destacado = 1
                  AND p.id_estado_producto = :estado_disponible
                ORDER BY p.id_producto DESC
                LIMIT :limite';

        return $this->ejecutar($sql, [
            ':estado_disponible' => [self::ESTADO_DISPONIBLE, PDO::PARAM_INT],
            ':limite'            => [$limite, PDO::PARAM_INT],
        ])->fetchAll();
    }
    // Consultas administrativas
    // (el control de acceso vive en el controlador/middleware, no aquí
    public function obtenerTodosAdmin(array $filtros = []): array
    {
        $sql = 'SELECT p.*, c.nombre_categoria, m.nombre_marca,
                       ep.nombre_estado AS estado_nombre
                FROM productos p
                INNER JOIN categorias c ON p.id_categoria = c.id_categoria
                INNER JOIN marcas m ON p.id_marca = m.id_marca
                INNER JOIN estados_producto ep ON p.id_estado_producto = ep.id_estado_producto
                WHERE 1 = 1';

        $params = [];

       $busqueda = $this->normalizarBusqueda($filtros['busqueda'] ?? null);
        if ($busqueda !== null) {
            $sql .= " AND (
                p.nombre LIKE :busqueda_nombre ESCAPE '!'
                OR p.codigo_modelo LIKE :busqueda_modelo ESCAPE '!'
                OR m.nombre_marca LIKE :busqueda_marca ESCAPE '!'
            )";

            $params[':busqueda_nombre'] = [$busqueda, PDO::PARAM_STR];
            $params[':busqueda_modelo'] = [$busqueda, PDO::PARAM_STR];
            $params[':busqueda_marca'] = [$busqueda, PDO::PARAM_STR];
        }

        $categoria = $this->idPositivoONulo($filtros['categoria'] ?? null);
        if ($categoria !== null) {
            $sql .= ' AND p.id_categoria = :categoria';
            $params[':categoria'] = [$categoria, PDO::PARAM_INT];
        }

        $estado = $this->idPositivoONulo($filtros['estado'] ?? null);
        if ($estado !== null) {
            $sql .= ' AND p.id_estado_producto = :estado';
            $params[':estado'] = [$estado, PDO::PARAM_INT];
        }

        $sql .= ' ORDER BY ' . $this->ordenSeguro($filtros['orden'] ?? null);

        [$limite, $offset] = $this->paginacion($filtros);
        $sql .= ' LIMIT :limite OFFSET :offset';
        $params[':limite'] = [$limite, PDO::PARAM_INT];
        $params[':offset'] = [$offset, PDO::PARAM_INT];

        return $this->ejecutar($sql, $params)->fetchAll();
    }

    // Obtiene un producto por ID incluyendo marca, categoría y estado (vista admin).
    public function obtenerPorId(int $idProducto): ?array
    {
        if ($idProducto <= 0) {
            return null;
        }

        $sql = 'SELECT p.*, c.nombre_categoria, m.nombre_marca,
                       ep.nombre_estado AS estado_nombre
                FROM productos p
                INNER JOIN categorias c ON p.id_categoria = c.id_categoria
                INNER JOIN marcas m ON p.id_marca = m.id_marca
                INNER JOIN estados_producto ep ON p.id_estado_producto = ep.id_estado_producto
                WHERE p.id_producto = :id
                LIMIT 1';

        $producto = $this->ejecutar($sql, [
            ':id' => [$idProducto, PDO::PARAM_INT],
        ])->fetch();

        return $producto ?: null;
    }

    // Devuelve los estados de producto existentes (consulta sin entrada del usuario).
    public function obtenerEstados(): array
    {
        return $this->db->query(
            'SELECT id_estado_producto, nombre_estado
             FROM estados_producto
             ORDER BY id_estado_producto ASC'
        )->fetchAll();
    }

    // Comprueba que el código/modelo no se repita.
    public function existeCodigoModelo(string $codigoModelo, ?int $excluirId = null): bool
    {
        $sql = 'SELECT 1 FROM productos WHERE codigo_modelo = :codigo_modelo';
        $params = [':codigo_modelo' => [trim($codigoModelo), PDO::PARAM_STR]];

        if ($excluirId !== null && $excluirId > 0) {
            $sql .= ' AND id_producto <> :id_producto';
            $params[':id_producto'] = [$excluirId, PDO::PARAM_INT];
        }

        $sql .= ' LIMIT 1';

        return (bool) $this->ejecutar($sql, $params)->fetchColumn();
    }

    // Escritura
    public function crear(array $datos): int
    {
        $datos = $this->validarDatos($datos);

        $sql = 'INSERT INTO productos
                    (id_categoria, id_marca, id_estado_producto, codigo_modelo,
                     nombre, descripcion, especificaciones, precio, stock, imagen, destacado)
                VALUES
                    (:id_categoria, :id_marca, :id_estado_producto, :codigo_modelo,
                     :nombre, :descripcion, :especificaciones, :precio, :stock, :imagen, :destacado)';

        $this->ejecutar($sql, $this->parametrosProducto($datos));

        return (int) $this->db->lastInsertId();
    }

    // Actualiza un producto existente.
    public function actualizar(int $idProducto, array $datos): bool
    {
        if ($idProducto <= 0) {
            throw new InvalidArgumentException('ID de producto inválido.');
        }

        $datos  = $this->validarDatos($datos);
        $params = $this->parametrosProducto($datos);
        $params[':id_producto'] = [$idProducto, PDO::PARAM_INT];

        $sql = 'UPDATE productos
                SET id_categoria       = :id_categoria,
                    id_marca           = :id_marca,
                    id_estado_producto = :id_estado_producto,
                    codigo_modelo      = :codigo_modelo,
                    nombre             = :nombre,
                    descripcion        = :descripcion,
                    especificaciones   = :especificaciones,
                    precio             = :precio,
                    stock              = :stock,
                    imagen             = :imagen,
                    destacado          = :destacado
                WHERE id_producto = :id_producto
                LIMIT 1';

        return $this->ejecutar($sql, $params)->rowCount() >= 0;
    }

    // Eliminación lógica: conserva el registro y lo marca como Descontinuado.
    public function descontinuar(int $idProducto): bool
    {
        if ($idProducto <= 0) {
            return false;
        }

        $sql = 'UPDATE productos
                SET id_estado_producto = :estado_descontinuado,
                    destacado = 0
                WHERE id_producto = :id_producto
                LIMIT 1';

        return $this->ejecutar($sql, [
            ':estado_descontinuado' => [self::ESTADO_DESCONTINUADO, PDO::PARAM_INT],
            ':id_producto'          => [$idProducto, PDO::PARAM_INT],
        ])->rowCount() > 0;
    }

    // Helpers internos de seguridad
    private function ejecutar(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->db->prepare($sql);

        foreach ($params as $marcador => [$valor, $tipo]) {
            $stmt->bindValue($marcador, $valor, $tipo);
        }

        $stmt->execute();

        return $stmt;
    }

    // Convierte a entero positivo o devuelve null si el filtro no aplica.
    private function idPositivoONulo(mixed $valor): ?int
    {
        if ($valor === null || $valor === '' || !is_numeric($valor)) {
            return null;
        }

        $entero = (int) $valor;

        return $entero > 0 ? $entero : null;
    }

    // Convierte a decimal válido (como string, para no perder precisión en DECIMAL).
    private function decimalONulo(mixed $valor): ?string
    {
        if ($valor === null || $valor === '' || !is_numeric($valor)) {
            return null;
        }

        $numero = (float) $valor;

        return $numero >= 0 ? number_format($numero, 2, '.', '') : null;
    }

    //Normaliza el término de búsqueda y escapa los comodines de LIKE.
    private function normalizarBusqueda(mixed $valor): ?string
    {
        if (!is_string($valor)) {
            return null;
        }

        $texto = trim($valor);
        if ($texto === '') {
            return null;
        }

        // Limita la longitud: entradas gigantes son un vector de denegación de servicio.
        $texto = mb_substr($texto, 0, 100);

        $texto = strtr(
            $texto,
            [
                '!' => '!!',
                '%' => '!%',
                '_' => '!_',
            ]
        );

        return '%' . $texto . '%';
    }

    // Resuelve el ORDER BY contra la lista blanca; cualquier otra cosa cae al valor por defecto.
    private function ordenSeguro(mixed $clave): string
    {
        if (is_string($clave) && isset(self::ORDENES_PERMITIDOS[$clave])) {
            return self::ORDENES_PERMITIDOS[$clave];
        }

        return self::ORDENES_PERMITIDOS['recientes'];
    }

    // Calcula límite y offset acotados.
    private function paginacion(array $filtros): array
    {
        $limite = $this->acotarLimite((int) ($filtros['limite'] ?? self::LIMITE_DEFECTO));
        $pagina = max(1, (int) ($filtros['pagina'] ?? 1));

        return [$limite, ($pagina - 1) * $limite];
    }

    private function acotarLimite(int $limite): int
    {
        return max(self::LIMITE_MINIMO, min(self::LIMITE_MAXIMO, $limite));
    }

    //Valida y normaliza los datos de escritura.
    private function validarDatos(array $datos): array
    {
        $requeridos = ['id_categoria', 'id_marca', 'id_estado_producto', 'codigo_modelo', 'nombre', 'precio'];

        foreach ($requeridos as $campo) {
            if (!isset($datos[$campo]) || $datos[$campo] === '') {
                throw new InvalidArgumentException("El campo «{$campo}» es obligatorio.");
            }
        }

        $estado = (int) $datos['id_estado_producto'];
        $estadosValidos = [self::ESTADO_DISPONIBLE, self::ESTADO_AGOTADO, self::ESTADO_DESCONTINUADO];

        if (!in_array($estado, $estadosValidos, true)) {
            throw new InvalidArgumentException('Estado de producto inválido.');
        }

        return [
            'id_categoria'       => (int) $datos['id_categoria'],
            'id_marca'           => (int) $datos['id_marca'],
            'id_estado_producto' => $estado,
            'codigo_modelo'      => mb_substr(trim((string) $datos['codigo_modelo']), 0, 50),
            'nombre'             => mb_substr(trim((string) $datos['nombre']), 0, 150),
            'descripcion'        => trim((string) ($datos['descripcion'] ?? '')),
            'especificaciones'   => trim((string) ($datos['especificaciones'] ?? '')),
            'precio'             => number_format(max(0, (float) $datos['precio']), 2, '.', ''),
            'stock'              => max(0, (int) ($datos['stock'] ?? 0)),
            'imagen'             => mb_substr(trim((string) ($datos['imagen'] ?? '')), 0, 255),
            'destacado'          => !empty($datos['destacado']) ? 1 : 0,
        ];
    }

    // Mapea los datos ya validados a parámetros tipados.
    private function parametrosProducto(array $datos): array
    {
        return [
            ':id_categoria'       => [$datos['id_categoria'],       PDO::PARAM_INT],
            ':id_marca'           => [$datos['id_marca'],           PDO::PARAM_INT],
            ':id_estado_producto' => [$datos['id_estado_producto'], PDO::PARAM_INT],
            ':codigo_modelo'      => [$datos['codigo_modelo'],      PDO::PARAM_STR],
            ':nombre'             => [$datos['nombre'],             PDO::PARAM_STR],
            ':descripcion'        => [$datos['descripcion'],        PDO::PARAM_STR],
            ':especificaciones'   => [$datos['especificaciones'],   PDO::PARAM_STR],
            ':precio'             => [$datos['precio'],             PDO::PARAM_STR],
            ':stock'              => [$datos['stock'],              PDO::PARAM_INT],
            ':imagen'             => [$datos['imagen'],             PDO::PARAM_STR],
            ':destacado'          => [$datos['destacado'],          PDO::PARAM_INT],
        ];
    }
}