-- ESQUEMA DE BASE DE DATOS: TIENDA EN LINEA DE ELECTRODOMESTICOS Y LINEA BLANCA

CREATE DATABASE IF NOT EXISTS `tienda_electrodomesticos` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `tienda_electrodomesticos`;

-- Desactivar temporalmente revision de llaves foraneas para creacion limpia
SET FOREIGN_KEY_CHECKS = 0;

-- 1. TABLA: roles
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
    `id_rol` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre_rol` VARCHAR(50) NOT NULL UNIQUE,
    `descripcion` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABLA: estados_usuario
DROP TABLE IF EXISTS `estados_usuario`;
CREATE TABLE `estados_usuario` (
    `id_estado_usuario` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre_estado` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABLA: usuarios
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
    `id_usuario` INT AUTO_INCREMENT PRIMARY KEY,
    `id_rol` INT NOT NULL,
    `id_estado_usuario` INT NOT NULL DEFAULT 1,
    `nombre` VARCHAR(100) NOT NULL,
    `apellido` VARCHAR(100) NOT NULL,
    `correo` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `telefono` VARCHAR(20) NULL,
    `direccion` TEXT NULL,
    `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_usuario_estado` FOREIGN KEY (`id_estado_usuario`) REFERENCES `estados_usuario` (`id_estado_usuario`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TABLA: categorias
DROP TABLE IF EXISTS `categorias`;
CREATE TABLE `categorias` (
    `id_categoria` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre_categoria` VARCHAR(100) NOT NULL UNIQUE,
    `descripcion` TEXT NULL,
    `imagen` VARCHAR(255) NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. TABLA: marcas
DROP TABLE IF EXISTS `marcas`;
CREATE TABLE `marcas` (
    `id_marca` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre_marca` VARCHAR(100) NOT NULL UNIQUE,
    `activo` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. TABLA: estados_producto
DROP TABLE IF EXISTS `estados_producto`;
CREATE TABLE `estados_producto` (
    `id_estado_producto` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre_estado` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. TABLA: productos
DROP TABLE IF EXISTS `productos`;
CREATE TABLE `productos` (
    `id_producto` INT AUTO_INCREMENT PRIMARY KEY,
    `id_categoria` INT NOT NULL,
    `id_marca` INT NOT NULL,
    `id_estado_producto` INT NOT NULL DEFAULT 1,
    `codigo_modelo` VARCHAR(50) NOT NULL UNIQUE,
    `nombre` VARCHAR(200) NOT NULL,
    `descripcion` TEXT NULL,
    `especificaciones` TEXT NULL,
    `precio` DECIMAL(10, 2) NOT NULL,
    `stock` INT NOT NULL DEFAULT 0,
    `imagen` VARCHAR(255) NULL,
    `destacado` TINYINT(1) NOT NULL DEFAULT 0,
    `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_producto_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_producto_marca` FOREIGN KEY (`id_marca`) REFERENCES `marcas` (`id_marca`) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_producto_estado` FOREIGN KEY (`id_estado_producto`) REFERENCES `estados_producto` (`id_estado_producto`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. TABLA: metodos_pago
DROP TABLE IF EXISTS `metodos_pago`;
CREATE TABLE `metodos_pago` (
    `id_metodo_pago` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre_metodo` VARCHAR(100) NOT NULL UNIQUE,
    `activo` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. TABLA: estados_pedido
DROP TABLE IF EXISTS `estados_pedido`;
CREATE TABLE `estados_pedido` (
    `id_estado_pedido` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre_estado` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. TABLA: pedidos
DROP TABLE IF EXISTS `pedidos`;
CREATE TABLE `pedidos` (
    `id_pedido` INT AUTO_INCREMENT PRIMARY KEY,
    `numero_pedido` VARCHAR(50) NOT NULL UNIQUE,
    `id_usuario` INT NOT NULL,
    `id_metodo_pago` INT NOT NULL,
    `id_estado_pedido` INT NOT NULL DEFAULT 1,
    `fecha_pedido` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `subtotal` DECIMAL(10, 2) NOT NULL,
    `impuesto` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `total` DECIMAL(10, 2) NOT NULL,
    `direccion_envio` TEXT NOT NULL,
    `notas` TEXT NULL,
    CONSTRAINT `fk_pedido_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_pedido_metodo_pago` FOREIGN KEY (`id_metodo_pago`) REFERENCES `metodos_pago` (`id_metodo_pago`) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_pedido_estado` FOREIGN KEY (`id_estado_pedido`) REFERENCES `estados_pedido` (`id_estado_pedido`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. TABLA: detalle_pedido
DROP TABLE IF EXISTS `detalle_pedido`;
CREATE TABLE `detalle_pedido` (
    `id_detalle` INT AUTO_INCREMENT PRIMARY KEY,
    `id_pedido` INT NOT NULL,
    `id_producto` INT NOT NULL,
    `cantidad` INT NOT NULL,
    `precio_unitario` DECIMAL(10, 2) NOT NULL,
    `subtotal` DECIMAL(10, 2) NOT NULL,
    CONSTRAINT `fk_detalle_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_detalle_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. TABLA: resenas
DROP TABLE IF EXISTS `resenas`;
CREATE TABLE `resenas` (
    `id_resena` INT AUTO_INCREMENT PRIMARY KEY,
    `id_usuario` INT NOT NULL,
    `id_producto` INT NOT NULL,
    `calificacion` TINYINT NOT NULL CHECK (`calificacion` BETWEEN 1 AND 5),
    `comentario` TEXT NULL,
    `fecha` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_resena_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_resena_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. TABLA: wishlist (Lista de Deseos)
DROP TABLE IF EXISTS `wishlist`;
CREATE TABLE `wishlist` (
    `id_wishlist` INT AUTO_INCREMENT PRIMARY KEY,
    `id_usuario` INT NOT NULL,
    `id_producto` INT NOT NULL,
    `fecha_agregado` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_usuario_producto_wishlist` (`id_usuario`, `id_producto`),
    CONSTRAINT `fk_wishlist_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_wishlist_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reactivar revision de llaves foraneas
SET FOREIGN_KEY_CHECKS = 1;
