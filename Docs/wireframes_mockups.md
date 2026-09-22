# Documento de Wireframes y Mockups de Interfaz (UX/UI)

## Proyecto: Tienda en Línea de Electrodomésticos y Línea Blanca

Este documento define la arquitectura visual, distribución de espacios y jerarquía de componentes para el frontend y panel administrativo, respetando los principios de usabilidad, contraste, diseño responsivo y sin uso de emojis.

---

## 1. Paleta de Colores y Tipografía del Sistema de Diseño

| Elemento | Color / Token | Hexadecimal | Uso |
|---|---|---|---|
| **Fondo Principal** | Slate Neutral Light | `#f8fafc` | Fondo general de páginas |
| **Encabezados / Navbar** | Slate Dark Navy | `#0f172a` | Barra de navegación superior y pie de página |
| **Color Primario (Acción)** | Indigo Sapphire | `#4338ca` | Botones principales, acentos de selección, tabs activas |
| **Color Secundario / Acento**| Amber Warm | `#d97706` | Badges de ofertas, estrellas de valoración, alertas de stock |
| **Color de Éxito** | Emerald Green | `#059669` | Estado completado, confirmación de pedido, botón de checkout |
| **Color de Peligro / Alerta** | Crimson Red | `#dc2626` | Botón eliminar, stock agotado, cancelación |
| **Tipografía de Títulos** | **Outfit (Google Fonts)** | 600/700 | Títulos h1-h4, marcas, precios destacados |
| **Tipografía de Contenido** | **Plus Jakarta Sans** | 400/500 | Párrafos, tablas, formularios, botones |

---

## 2. Wireframe 1: Página Principal (Home)

```
+-------------------------------------------------------------------------------+
| [Logo: Doméstik]   [Buscar electrodomésticos...] (Q)  [Wishlist (2)] [Cart (1)] [Login] |
| Nav: Inicio | Refrigeración | Lavado | Cocina | Climatización | Pequeños | Ofertas      |
+-------------------------------------------------------------------------------+
| HERO BANNER CAROUSEL                                                          |
| "Equipa tu Hogar con Tecnología Inverter"                                     |
| Hasta 30% de descuento en Línea Blanca seleccionada.                          |
| [Ver Catálogo de Ofertas]                                                     |
+-------------------------------------------------------------------------------+
| CATEGORÍAS PRINCIPALES                                                        |
| +-------------+  +-------------+  +-------------+  +-------------+  +---------+ |
| | [Img Refri] |  | [Img Lavad] |  | [Img Estuf] |  | [Img Micro] |  | [Img AC]| |
| |Refrigeración|  |Lavado/Secado|  |   Cocción   |  |  Pequeños   |  | Climat. | |
| +-------------+  +-------------+  +-------------+  +-------------+  +---------+ |
+-------------------------------------------------------------------------------+
| PRODUCTOS DESTACADOS EN OFERTA                                                |
| +-----------------+  +-----------------+  +-----------------+  +--------------+ |
| | [Img Samsung]   |  | [Img LG]        |  | [Img Whirlpool] |  | [Img Oster]  | |
| | Marca: Samsung  |  | Marca: LG       |  | Marca: Whirlpool|  | Marca: Oster | |
| | Refri 27ft Space|  | Lavadora 20 Kg  |  | Estufa Gas 30"  |  | Licuadora Pro| |
| | Q 8,499.00      |  | Q 4,699.00      |  | Q 3,899.00      |  | Q 749.00     | |
| | [Ver Detalle]   |  | [Ver Detalle]   |  | [Ver Detalle]   |  | [Ver Detalle]| |
| | [Añadir Carro]  |  | [Añadir Carro]  |  | [Añadir Carro]  |  | [Añadir Carro| |
| +-----------------+  +-----------------+  +-----------------+  +--------------+ |
+-------------------------------------------------------------------------------+
| [Footer: Enlaces rápidos | Categorías | Métodos de Pago Aceptados | Derechos 2026] |
+-------------------------------------------------------------------------------+
```

---

## 3. Wireframe 2: Catálogo con Barra Lateral de Filtros (Filtros Asíncronos)

```
+-------------------------------------------------------------------------------+
| HEADER & NAVBAR                                                               |
+-------------------------------------------------------------------------------+
| Migas de Pan: Inicio > Catálogo de Electrodomésticos                           |
|-------------------------------------------------------------------------------|
| FILTROS LATERALES        | LISTADO DE PRODUCTOS (Mostrando 9 productos)       |
|                          | Ordenar por: [Menor Precio | Mayor Precio | Novedad]|
| [Limpiar Filtros]        |----------------------------------------------------|
|                          | +---------------+  +---------------+  +------------+
| Categorías               | | [Foto Refri]  |  | [Foto Lavad]  |  | [Foto AC]  |
| [x] Refrigeración (2)    | | Samsung 27ft  |  | LG 20 Kg      |  | Samsung 12K|
| [ ] Lavado y Secado (2)  | | Inverter A+   |  | TurboWash     |  | FastCooling|
| [ ] Cocción (2)          | | Q 8,499.00    |  | Q 4,699.00    |  | Q 3,599.00 |
| [ ] Pequeños (2)         | | (*****) (5.0) |  | (**** ) (4.8) |  | (*****)    |
|                          | | [Añadir Carro]|  | [Añadir Carro]|  | [Añadir]   |
| Marcas                   | +---------------+  +---------------+  +------------+
| [x] Samsung              | +---------------+  +---------------+  +------------+
| [ ] LG                   | | [Foto Oster]  |  | [Foto Horno]  |  | [Airfryer] |
| [ ] Whirlpool            | | Licuadora Pro |  | Horno Bosch   |  | B&D 5.7L   |
| [ ] Bosch                | | Q 749.00      |  | Q 11,299.00   |  | Q 899.00   |
|                          | | [Añadir Carro]|  | [Añadir Carro]|  | [Añadir]   |
| Rango de Precio          | +---------------+  +---------------+  +------------+
| Min: Q 500  Max: Q 15000 | [ 1 ] [ 2 ] [ Siguiente ]                          |
| [Deslizador de Precio]   |                                                    |
+-------------------------------------------------------------------------------+
```

---

## 4. Wireframe 3: Detalle del Producto y Reseñas

```
+-------------------------------------------------------------------------------+
| Migas de Pan: Inicio > Refrigeración > Samsung 27ft SpaceMax                  |
+-------------------------------------------------------------------------------+
| +---------------------------+ | TITULO: Refrigeradora Samsung 27ft SpaceMax    |
| |                           | | Marca: Samsung  |  Modelo: RS27T5200SR         |
| |                           | | Valoración: [*****] (12 Reseñas de Clientes)   |
| |      IMAGEN PRINCIPAL     | | Estado: [En Stock (15 Disponibles)]            |
| |     ALTA RESOLUCIÓN       | | PRECIO: Q 8,499.00                             |
| |                           | |------------------------------------------------|
| |                           | | ESPECIFICACIONES CLAVE:                        |
| +---------------------------+ | - Tecnología Inverter con 10 años garantía     |
| [Mini 1] [Mini 2] [Mini 3]   | - Dispensador de agua y hielo exterior          |
|                               | - Acabado Acero Inoxidable antihuellas         |
|                               |------------------------------------------------|
|                               | Cantidad: [-] [ 1 ] [+]                        |
|                               | [ Añadir al Carrito ]   [ Guardar en Wishlist ]|
+-------------------------------------------------------------------------------+
| TABS: [ Descripción Detallada ] [ Ficha Técnica ] [ Reseñas de Clientes (12) ] |
|-------------------------------------------------------------------------------|
| Formulario para Dejar Reseña (Solo compradores verificados):                  |
| Calificación: [*] [*] [*] [*] [*]                                             |
| Comentario: [                                                               ] |
| [ Publicar Reseña ]                                                           |
+-------------------------------------------------------------------------------+
```

---

## 5. Wireframe 4: Carrito y Checkout Transaccional (ACID)

```
+-------------------------------------------------------------------------------+
| CARRITO DE COMPRAS Y RESUMEN                                                  |
+-------------------------------------------------------------------------------+
| PRODUCTOS EN EL CARRITO (2)                  | RESUMEN DE LA ORDEN            |
|----------------------------------------------|--------------------------------|
| [Img] Refrigeradora Samsung 27ft             | Subtotal:           Q 8,499.00 |
|       Precio: Q 8,499.00                     | Impuesto (12% IVA): Q 1,019.88 |
|       Cant: [-] [ 1 ] [+]  Total: Q 8,499.00 | Envío:                    Gratis |
|       [Eliminar]                             | TOTAL:              Q 9,518.88 |
|                                              |--------------------------------|
| [Img] Licuadora Oster Pro 800W               | DATOS DE ENTREGA Y PAGO:       |
|       Precio: Q 749.00                       | Dirección: [ Av. Reforma 10-00]|
|       Cant: [-] [ 2 ] [+]  Total: Q 1,498.00 | Teléfono:  [ 5555-1234        ]|
|       [Eliminar]                             | Método de Pago:                |
|                                              | (o) Tarjeta Crédito / Débito   |
|                                              | ( ) Transferencia Bancaria     |
|                                              | ( ) Pago Contra Entrega        |
|                                              |--------------------------------|
|                                              | [ Confirmar Pedido Transaccional ] |
+-------------------------------------------------------------------------------+
```

---

## 6. Wireframe 5: Panel de Administración (Backoffice)

```
+-------------------------------------------------------------------------------+
| [ElectroAdmin]   [Ver Tienda]                      [Admin: Carlos] [Cerrar Sesión]|
+-------------------------------------------------------------------------------+
| MENU LATERAL      | DASHBOARD PRINCIPAL                                       |
| - Dashboard       | +----------------+ +----------------+ +-----------------+ |
| - Productos (CRUD)| | Ventas del Mes | | Pedidos Pend.  | | Total Productos | |
| - Categorías      | | Q 45,890.00    | | 4 Órdenes      | | 9 Activos       | |
| - Marcas          | +----------------+ +----------------+ +-----------------+ |
| - Pedidos         |-----------------------------------------------------------|
| - Usuarios        | GESTIÓN RÁPIDA DE INVENTARIO Y PEDIDOS                    |
| - Reportes        | [+ Nuevo Producto]   [Buscar producto en almacén...]       |
|                   | +----+----------------------+----------+-------+--------+ |
|                   | |ID  | Producto             | Categoría| Stock | Acción | |
|                   | |#1  | Refri Samsung 27ft   | Refrig.  | 15    | [Edit] | |
|                   | |#2  | Lavadora LG 20 Kg    | Lavado   | 12    | [Edit] | |
|                   | |#3  | Licuadora Oster Pro  | Pequeños | 40    | [Edit] | |
|                   | +----+----------------------+----------+-------+--------+ |
+-------------------------------------------------------------------------------+
```
