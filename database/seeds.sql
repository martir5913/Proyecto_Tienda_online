-- DATOS SEMILLA (SEEDS): TIENDA EN LINEA DE ELECTRODOMESTICOS Y LINEA BLANCA

USE `tienda_electrodomesticos`;

-- 1. ROLES
INSERT INTO `roles` (`id_rol`, `nombre_rol`, `descripcion`) VALUES
(1, 'Administrador', 'Control total de productos, categorías, pedidos y reportes'),
(2, 'Cliente', 'Usuario comprador con acceso a catálogo, carrito, pedidos y reseñas')
ON DUPLICATE KEY UPDATE `nombre_rol` = VALUES(`nombre_rol`);

-- 2. ESTADOS DE USUARIO
INSERT INTO `estados_usuario` (`id_estado_usuario`, `nombre_estado`) VALUES
(1, 'Activo'),
(2, 'Inactivo'),
(3, 'Bloqueado')
ON DUPLICATE KEY UPDATE `nombre_estado` = VALUES(`nombre_estado`);

-- 3. USUARIOS INICIALES (Contraseñas: admin123 y cliente123)
INSERT INTO `usuarios` (`id_usuario`, `id_rol`, `id_estado_usuario`, `nombre`, `apellido`, `correo`, `password`, `telefono`, `direccion`) VALUES
(1, 1, 1, 'Carlos', 'Administrador', 'admin@electrotienda.com', '$2y$10$NTqn.QGt6t3O39xlnu3jNO9.qt7SAZZVx5n5nMYZAfWb6JAD9O.7q', '5555-1234', 'Oficina Central Ciudad'),
(2, 2, 1, 'Maria', 'Gonzalez', 'maria.cliente@gmail.com', '$2y$10$prWij5zm1W1uT6HtBFEvpu4eyXgWrZQ/kK7S1Y3ZXfOHZ7nsarqgu', '5555-5678', 'Av. Las Americas 14-20 Zona 10')
ON DUPLICATE KEY UPDATE `correo` = VALUES(`correo`);

-- 4. CATEGORIAS DE ELECTRODOMESTICOS Y LINEA BLANCA
INSERT INTO `categorias` (`id_categoria`, `nombre_categoria`, `descripcion`, `imagen`, `activo`) VALUES
(1, 'Refrigeración', 'Refrigeradoras inverter, side by side, francesas y congeladores verticales', 'cat_refrigeracion.jpg', 1),
(2, 'Lavado y Secado', 'Lavadoras automáticas, centros de lavado y secadoras de alta eficiencia', 'cat_lavado.jpg', 1),
(3, 'Cocción y Estufas', 'Estufas de gas, hornos empotrables, parrillas de inducción y campanas', 'cat_coccion.jpg', 1),
(4, 'Pequeños Electrodomésticos', 'Microondas, freidoras de aire, licuadoras, cafeteras y procesadores', 'cat_pequenos.jpg', 1),
(5, 'Climatización', 'Aires acondicionados inverter, deshumidificadores y ventiladores de torre', 'cat_climatizacion.jpg', 1)
ON DUPLICATE KEY UPDATE `nombre_categoria` = VALUES(`nombre_categoria`);

-- 5. MARCAS
INSERT INTO `marcas` (`id_marca`, `nombre_marca`, `activo`) VALUES
(1, 'Samsung', 1),
(2, 'LG', 1),
(3, 'Whirlpool', 1),
(4, 'Mabe', 1),
(5, 'Bosch', 1),
(6, 'Oster', 1),
(7, 'Black+Decker', 1),
(8, 'Frigidaire', 1)
ON DUPLICATE KEY UPDATE `nombre_marca` = VALUES(`nombre_marca`);

-- 6. ESTADOS DE PRODUCTO
INSERT INTO `estados_producto` (`id_estado_producto`, `nombre_estado`) VALUES
(1, 'Disponible'),
(2, 'Agotado'),
(3, 'Descontinuado')
ON DUPLICATE KEY UPDATE `nombre_estado` = VALUES(`nombre_estado`);

-- 7. METODOS DE PAGO
INSERT INTO `metodos_pago` (`id_metodo_pago`, `nombre_metodo`, `activo`) VALUES
(1, 'Tarjeta de Crédito / Débito', 1),
(2, 'Transferencia Bancaria', 1),
(3, 'Pago Contra Entrega', 1)
ON DUPLICATE KEY UPDATE `nombre_metodo` = VALUES(`nombre_metodo`);

-- 8. ESTADOS DE PEDIDO
INSERT INTO `estados_pedido` (`id_estado_pedido`, `nombre_estado`) VALUES
(1, 'Pendiente'),
(2, 'Procesando'),
(3, 'Enviado'),
(4, 'Entregado'),
(5, 'Cancelado')
ON DUPLICATE KEY UPDATE `nombre_estado` = VALUES(`nombre_estado`);

-- 9. PRODUCTOS DE PRUEBA
INSERT INTO `productos` (`id_producto`, `id_categoria`, `id_marca`, `id_estado_producto`, `codigo_modelo`, `nombre`, `descripcion`, `especificaciones`, `precio`, `stock`, `imagen`, `destacado`) VALUES
(1, 1, 1, 1, 'RS27T5200SR', 'Refrigeradora Side by Side 27 Pies SpaceMax', 'Refrigeradora con tecnología SpaceMax que ofrece mayor espacio interior sin aumentar dimensiones exteriores. Acabado en acero inoxidable antihuellas.', 'Capacidad: 27 cu.ft | Voltaje: 120V | Tecnología Inverter | Dispensador de agua y hielo', 8499.00, 15, 'prod_refrig_samsung_27.jpg', 1),
(2, 1, 2, 1, 'LT44BVP', 'Refrigeradora Top Freezer 16 Pies DoorCooling+', 'Refrigeradora con enfriamiento uniforme y rápido gracias a DoorCooling+. Compresor Smart Inverter con 10 años de garantía.', 'Capacidad: 16 cu.ft | Eficiencia energética A+ | Filtro higiénico | Iluminación LED', 5299.00, 20, 'prod_refrig_lg_16.jpg', 1),
(3, 2, 3, 1, '8MWTW2024MJM', 'Lavadora Carga Superior 20 Kg Xpert System', 'Lavadora con agitador Double Action y sistema Xpert System para remover manchas difíciles con ciclos automáticos.', 'Capacidad: 20 Kg | 12 ciclos de lavado | Canasta 100% de acero inoxidable', 4699.00, 12, 'prod_lavadora_whirlpool_20.jpg', 1),
(4, 2, 2, 1, 'WM22VV2S6BR', 'Centro de Lavado WashTower 22 Kg Carga Frontal', 'Torre de lavado y secado integrada en un solo panel de control central. Conectividad ThinQ y tecnología AI DD para cuidado de prendas.', 'Capacidad lavado: 22 Kg | Capacidad secado: 22 Kg | Motor Direct Drive Inverter', 14899.00, 8, 'prod_washtower_lg.jpg', 1),
(5, 3, 4, 1, 'EM7654BFIS0', 'Estufa de Gas 30 Pulgadas con Encendido Electrónico', 'Estufa con cubierta de acero inoxidable, 6 quemadores sellados (incluyendo jumbo) y horno con ventana panorámica.', 'Tamaño: 30 pulgadas | 6 quemadores Tech | Termocontrol en horno | Capelo de cristal templado', 3899.00, 18, 'prod_estufa_mabe_30.jpg', 1),
(6, 3, 5, 1, 'HBL8451UC', 'Horno Eléctrico Empotrable 30 Pulgadas Serie 800', 'Horno empotrable de convección genuina europea para horneado homogéneo. Puerta QuietClose y autolimpieza pirolítica.', 'Potencia: 3800W | Convección Europea | 14 modos de cocción | Acero inoxidable grado 304', 11299.00, 5, 'prod_horno_bosch_30.jpg', 0),
(7, 4, 6, 1, 'BLSTPEG-G8R', 'Licuadora Reversible Pro 800W Jarra de Vidrio', 'Licuadora de alto rendimiento con motor reversible que gira en ambas direcciones para licuados y triturados perfectos.', 'Potencia: 800W | Jarra de vidrio Boroclass 1.5L | Acople 100% metálico All-Metal Drive', 749.00, 40, 'prod_licuadora_oster_pro.jpg', 1),
(8, 4, 7, 1, 'HF110SBD', 'Freidora de Aire Digital Extra Grande 5.7 Litros', 'Freidora de aire con doble ventilador por convección para alimentos crujientes con 85% menos grasa. Control digital táctil.', 'Capacidad: 5.7 Litros | Potencia: 1800W | 8 programas preestablecidos | Cesta antiadherente', 899.00, 25, 'prod_airfryer_blackdecker.jpg', 1),
(9, 5, 1, 1, 'AR12TXHQASINEU', 'Aire Acondicionado Split Inverter 12,000 BTU 220V', 'Aire acondicionado con Fast Cooling para enfriar rápidamente habitaciones y filtro HD antibacteriano de fácil limpieza.', 'Capacidad: 12,000 BTU | Eficiencia SEER 18 | Gas ecológico R410A | Modo Good Sleep', 3599.00, 14, 'prod_ac_samsung_12k.jpg', 1)
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

-- 10. RESEÑAS DE PRUEBA
INSERT INTO `resenas` (`id_resena`, `id_usuario`, `id_producto`, `calificacion`, `comentario`) VALUES
(1, 2, 1, 5, 'Excelente refrigeradora, enfría de forma silenciosa y tiene muchísimo espacio interior.'),
(2, 2, 7, 5, 'La licuadora tritura hielo sin problemas y la jarra de vidrio es muy resistente.')
ON DUPLICATE KEY UPDATE `calificacion` = VALUES(`calificacion`);

-- 11. WISHLIST DE PRUEBA
INSERT INTO `wishlist` (`id_usuario`, `id_producto`) VALUES
(2, 4),
(2, 9)
ON DUPLICATE KEY UPDATE `fecha_agregado` = CURRENT_TIMESTAMP;
