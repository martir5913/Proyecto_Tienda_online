-- =============================================================================
-- PROCEDIMIENTOS ALMACENADOS (STORED PROCEDURES / USP) - MYSQL 8.0
-- BD: tienda_electrodomesticos | Motor: InnoDB (Transacciones ACID)
-- =============================================================================

USE `tienda_electrodomesticos`;

DELIMITER $$

-- -----------------------------------------------------------------------------
-- 1. USP: usp_obtener_metricas_dashboard
-- Proposito: Calcula de forma centralizada los KPIs para el panel de administracion
-- -----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS `usp_obtener_metricas_dashboard`$$
CREATE PROCEDURE `usp_obtener_metricas_dashboard`()
BEGIN
    SELECT 
        (SELECT COALESCE(SUM(total), 0.00) FROM pedidos WHERE id_estado_pedido != 5) AS ventas_totales,
        (SELECT COUNT(*) FROM pedidos) AS total_pedidos,
        (SELECT COUNT(*) FROM pedidos WHERE id_estado_pedido = 1) AS pedidos_pendientes,
        (SELECT COUNT(*) FROM productos WHERE id_estado_producto = 1) AS total_productos_activos,
        (SELECT COUNT(*) FROM usuarios WHERE id_rol = 2) AS total_clientes,
        (SELECT COUNT(*) FROM productos WHERE stock <= 5 AND id_estado_producto = 1) AS productos_bajo_stock;
END$$

-- -----------------------------------------------------------------------------
-- 2. USP: usp_cambiar_estado_pedido
-- Proposito: Actualiza el estado de una orden. Si se cancela (estado 5), reintegra el stock.
-- -----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS `usp_cambiar_estado_pedido`$$
CREATE PROCEDURE `usp_cambiar_estado_pedido`(
    IN p_id_pedido INT,
    IN p_nuevo_estado INT,
    OUT p_resultado_codigo INT,
    OUT p_resultado_mensaje VARCHAR(255)
)
proc_label: BEGIN
    DECLARE v_estado_actual INT;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_resultado_codigo = 500;
        SET p_resultado_mensaje = 'Error interno al actualizar estado del pedido';
    END;

    -- Validar si el pedido existe
    SELECT id_estado_pedido INTO v_estado_actual 
    FROM pedidos 
    WHERE id_pedido = p_id_pedido;

    IF v_estado_actual IS NULL THEN
        SET p_resultado_codigo = 404;
        SET p_resultado_mensaje = 'El pedido solicitado no existe';
        LEAVE proc_label;
    END IF;

    START TRANSACTION;

    -- Si se cambia a Cancelado (5) y antes no estaba cancelado, reponer stock
    IF p_nuevo_estado = 5 AND v_estado_actual != 5 THEN
        UPDATE productos p
        INNER JOIN detalle_pedido dp ON p.id_producto = dp.id_producto
        SET p.stock = p.stock + dp.cantidad
        WHERE dp.id_pedido = p_id_pedido;
    END IF;

    -- Actualizar estado
    UPDATE pedidos 
    SET id_estado_pedido = p_nuevo_estado 
    WHERE id_pedido = p_id_pedido;

    COMMIT;

    SET p_resultado_codigo = 200;
    SET p_resultado_mensaje = 'Estado de pedido actualizado correctamente';
END$$

-- -----------------------------------------------------------------------------
-- 3. USP: usp_filtrar_catalogo
-- Proposito: Consulta optimizada de electrodomesticos con filtros opcionales
-- -----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS `usp_filtrar_catalogo`$$
CREATE PROCEDURE `usp_filtrar_catalogo`(
    IN p_id_categoria INT,
    IN p_id_marca INT,
    IN p_busqueda VARCHAR(100),
    IN p_precio_min DECIMAL(10,2),
    IN p_precio_max DECIMAL(10,2)
)
BEGIN
    SELECT 
        p.id_producto,
        p.codigo_modelo,
        p.nombre,
        p.descripcion,
        p.especificaciones,
        p.precio,
        p.stock,
        p.imagen,
        p.destacado,
        c.id_categoria,
        c.nombre_categoria,
        m.id_marca,
        m.nombre_marca,
        ep.nombre_estado AS estado_nombre,
        COALESCE(AVG(r.calificacion), 0) AS promedio_calificacion,
        COUNT(r.id_resena) AS total_resenas
    FROM productos p
    INNER JOIN categorias c ON p.id_categoria = c.id_categoria
    INNER JOIN marcas m ON p.id_marca = m.id_marca
    INNER JOIN estados_producto ep ON p.id_estado_producto = ep.id_estado_producto
    LEFT JOIN resenas r ON p.id_producto = r.id_producto
    WHERE p.id_estado_producto = 1
      AND (p_id_categoria IS NULL OR p_id_categoria = 0 OR p.id_categoria = p_id_categoria)
      AND (p_id_marca IS NULL OR p_id_marca = 0 OR p.id_marca = p_id_marca)
      AND (p_precio_min IS NULL OR p.precio >= p_precio_min)
      AND (p_precio_max IS NULL OR p.precio <= p_precio_max)
      AND (p_busqueda IS NULL OR p_busqueda = '' OR 
           p.nombre LIKE CONCAT('%', p_busqueda, '%') OR 
           p.codigo_modelo LIKE CONCAT('%', p_busqueda, '%') OR
           p.descripcion LIKE CONCAT('%', p_busqueda, '%'))
    GROUP BY p.id_producto
    ORDER BY p.id_producto DESC;
END$$

DELIMITER ;
