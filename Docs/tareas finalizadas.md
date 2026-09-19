# RF04 – Mostrar catálogo de productos

## Objetivo

Implementar la funcionalidad **RF04 – Mostrar catálogo de productos** en el proyecto `Proyecto_Tienda_online`


## Archivos modificados

### 1. `views/catalogo.php`

Se utilizó la vista del catálogo para mostrar los productos disponibles.

La vista mantiene la estructura visual actual del proyecto y trabaja con:

- Tarjetas de productos.
- Nombre del producto.
- Marca.
- Categoría.
- Precio.
- Stock disponible.
- Especificaciones resumidas.
- Botón **Ver detalles**.
- Botón para agregar al carrito.
- Modal para mostrar información completa del producto.

### 2. `public/js/catalogo.js`

Se agregó la lógica JavaScript correspondiente al catálogo.

#### Inicialización del módulo

```javascript
const CatalogoModulo = {
    init() {
        // Inicialización de elementos del catálogo
    }
};
```

El módulo se inicia cuando el DOM termina de cargar:

```javascript
document.addEventListener(
    'DOMContentLoaded',
    () => CatalogoModulo.init()
);
```

#### Filtros del catálogo

Se implementó el filtrado asíncrono mediante `fetch()` para trabajar con búsqueda, categoría, marca y precio máximo.

```javascript
const resp = await fetch(
    `${this.apiUrl}?${params.toString()}`,
    {
        headers: {
            'Accept': 'application/json'
        }
    }
);

const res = await resp.json();
```

#### Renderizado dinámico

Los productos recibidos desde la API se renderizan dinámicamente mostrando:

- Marca.
- Nombre.
- Categoría.
- Especificaciones.
- Disponibilidad.
- Precio.
- Botón **Ver detalles**.
- Botón para agregar al carrito.

#### Botón "Ver detalles"

Se agregó el evento para detectar qué producto seleccionó el usuario:

```javascript
this.gridProductos.addEventListener('click', (event) => {
    const botonDetalle = event.target.closest('.btn-detalle-producto');

    if (!botonDetalle) return;

    const idProducto = Number.parseInt(
        botonDetalle.dataset.productoId,
        10
    );

    if (Number.isInteger(idProducto) && idProducto > 0) {
        this.mostrarDetalle(idProducto);
    }
});
```

#### Modal de detalle del producto

Se utiliza Bootstrap Modal para mostrar:

- Nombre.
- Marca.
- Categoría.
- Modelo.
- Descripción.
- Especificaciones.
- Stock.
- Precio.
- Imagen.
- Botón **Añadir al carrito**.

La consulta se realiza mediante:

```text
api/productos.php?id=ID_PRODUCTO
```


Durante las pruebas se agregaron temporalmente estos registros:

```javascript
console.log('ID seleccionado:', idProducto);
```

```javascript
console.log('Respuesta API:', res);
```

Estos logs permitieron comprobar que:

1. El botón **Ver detalles** obtiene correctamente el ID.
2. `catalogo.js` realiza la petición a la API.
3. PHP devuelve correctamente el producto.
4. El modal recibe y muestra la información.

Ejemplo de respuesta comprobada:

```text
success: true
message: "Detalle del producto obtenido."
data: {...}
```

Los `console.log()` pueden eliminarse cuando termine la etapa de desarrollo

### 3. `views/layouts/footer.php`

Se modificó la carga del archivo JavaScript específico de cada vista para evitar que el navegador use una versión antigua almacenada en caché.

Código utilizado:

```php
<?php if (isset($scriptEspecifico)): ?>
    <?php
    $rutaScript = PUBLIC_DIR . '/js/' . $scriptEspecifico;
    $versionScript = file_exists($rutaScript) ? filemtime($rutaScript) : time();
    ?>
    <script src="<?= BASE_URL ?>/public/js/<?= $scriptEspecifico ?>?v=<?= $versionScript ?>"></script>
<?php endif; ?>
```

### Motivo del cambio

Antes el navegador podía seguir usando una versión anterior de:

```text
/public/js/catalogo.js
```

Ahora se genera una URL como:

```text
/public/js/catalogo.js?v=1789778959
```

Cuando `catalogo.js` cambia, también cambia el valor generado por `filemtime()`. Esto obliga al navegador a cargar la versión nueva.

Este fue el cambio que solucionó el problema donde **Ver detalles** no respondía aunque el código JavaScript ya hubiera sido actualizado.

## Imágenes de productos

Las imágenes se colocaron en:

```text
public/img/productos/
``` 
```text
Proyecto_Tienda_online/
└── public/
    └── img/
        └── productos/
            ├── prod_ac_samsung_12k.jpg
            ├── prod_estufa_mabe_30.jpg
            ├── prod_lavadora_whirlpool_20.jpg
            ├── prod_refrig_lg_16.jpg
            ├── prod_washtower_lg.jpg
            └── prod_horno_bosch_30.jpg
```

### Manejo de imágenes faltantes

Cuando una imagen no existe, el catálogo muestra un icono de producto como respaldo en lugar de dejar una imagen rota.

## Archivos que no fue necesario modificar

No fue necesario alterar la lógica principal de:

```text
index.php
app/models/Producto.php
app/controllers/ProductoController.php
api/productos.php
public/css/app.css
```

## Estructura final relacionada con RF04

```text
Proyecto_Tienda_online/
├── api/
│   └── productos.php
│
├── app/
│   ├── controllers/
│   │   └── ProductoController.php
│   └── models/
│       └── Producto.php
│
├── public/
│   ├── css/
│   │   └── app.css
│   ├── img/
│   │   └── productos/
│   │       └── imágenes de los productos
│   └── js/
│       └── catalogo.js
│
├── views/
│   ├── layouts/
│   │   └── footer.php
│   └── catalogo.php
│
└── index.php
```

---

## Actualización del inicio: productos destacados con imágenes y detalle

También se actualizó la vista principal del sistema:

```text
views/home.php

El objetivo fue hacer que los productos destacados del inicio utilizaran la misma información y funcionalidad del catálogo.

Mostrar imágenes de productos en el inicio

Anteriormente, los productos destacados mostraban solamente un icono de caja:

Botón "Ver detalles" en productos destacados

Se agregó el botón Ver detalles a las tarjetas de productos mostradas en la página principal.

La zona inferior de cada tarjeta quedó preparada para mostrar:

Precio.
Botón Ver detalles.
Botón agregar al carrito.

Código utilizado:

<div class="mt-auto pt-2 border-top">

    <div class="product-price mb-2">
        Q <?= number_format((float)$prod['precio'], 2) ?>
    </div>

    <div class="d-flex gap-2">

        <button
            type="button"
            class="btn btn-sm btn-outline-primary flex-grow-1 btn-detalle-producto"
            data-producto-id="<?= (int)$prod['id_producto'] ?>"
        >
            <i class="bi bi-eye me-1"></i>
            Ver detalles
        </button>

        <button
            type="button"
            class="btn btn-sm btn-primary-app"
            onclick="ElectroApp.agregarAlCarrito(<?= (int)$prod['id_producto'] ?>)"
        >
            <i class="bi bi-cart-plus"></i>
        </button>

    </div>

</div>


## Resultado final

La funcionalidad RF04 permite actualmente:

- Mostrar el catálogo de productos.
- Consultar productos desde la base de datos.
- Filtrar productos.
- Buscar productos.
- Mostrar disponibilidad y stock.
- Mostrar precios.
- Mostrar imágenes cuando existen.
- Mostrar un icono de respaldo cuando una imagen no existe.
- Abrir un modal con el detalle completo del producto.
- Obtener el detalle mediante una petición asíncrona a la API.
- Agregar productos al carrito desde la tarjeta o desde el modal.
- Evitar problemas de caché con `catalogo.js`.
- Reutilizar catalogo.js
