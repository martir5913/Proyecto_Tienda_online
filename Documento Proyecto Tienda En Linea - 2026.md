## **PROYECTO DE DESARROLLO WEB** 

# **Tienda en Línea de Productos** 

## **Datos Generales del Proyecto** 

Nombre del Proyecto: Tienda en Línea de Productos 

Tipo de Proyecto: Aplicación web de comercio electrónico 

Versión: 1.0 

**Fecha de inicio: 16 de septiembre 2026** 

**Fecha de Entrega: 25 de septiembre 20256** 

### **Proyecto: Tienda en Línea de Productos** 

### **1. Planteamiento del problema** 

### **1.1 Descripción del problema** 

Actualmente, los procesos tradicionales de compra de productos pueden presentar limitaciones relacionadas con la disponibilidad de información, consulta de productos, gestión de pedidos y seguimiento de las compras. Cuando estos procesos se realizan de forma manual o mediante canales que no están integrados, el cliente puede tener dificultades para consultar productos, conocer sus características, administrar su compra y verificar el estado de sus pedidos. 

Además, para el administrador resulta necesario contar con una herramienta que permita gestionar de manera centralizada los productos, categorías, usuarios, pedidos y demás información relacionada con la operación de la tienda. 

Por esta razón, se plantea el desarrollo de una **aplicación web de comercio electrónico** que permita administrar un catálogo de productos y ofrecer a los usuarios un proceso de compra sencillo, organizado y seguro. 

La solución permitirá centralizar la información de los productos y proporcionar diferentes funcionalidades para clientes y administradores, incluyendo registro de usuarios, inicio de sesión, búsqueda de productos, filtros, carrito de compras, pedidos, reseñas y lista de deseos. 

### **1.2 Solución propuesta** 

Se desarrollará una aplicación web denominada **Tienda en Línea de Productos** , utilizando una arquitectura cliente-servidor y el patrón de diseño **MVC (Modelo-Vista-Controlador)** . 

La aplicación permitirá: 

- Registrar usuarios. 

- Iniciar y cerrar sesión. 

- Consultar productos. 

- Buscar productos. 

- Filtrar productos por diferentes criterios. 

- Visualizar el detalle de cada producto. 

- Agregar productos al carrito. 

- Modificar cantidades. 

- Eliminar productos del carrito. 

- Registrar pedidos. 

- Consultar el historial de pedidos. 

- Agregar reseñas y calificaciones. 

- Administrar una lista de deseos. 

- Administrar productos, categorías y usuarios desde el área administrativa. 

- Interactuar con la base de datos mediante una API. 

### **2. Requerimientos del proyecto** 

Los requerimientos se organizarán utilizando un **Product Backlog** , que permitirá identificar las funcionalidades que debe desarrollar el sistema. 

### **2.1 Product Backlog** 

|ID|Requerimiento|Tipo|Prioridad|
|---|---|---|---|
|RF01|Registrar nuevos usuarios|Funcional|Alta|
|RF02|Iniciar y cerrar sesión|Funcional|Alta|
|RF03|Recuperar contraseña|Funcional|Media|
|RF04|Mostrar catálogo de productos|Funcional|Alta|
|RF05|Buscar productos|Funcional|Alta|
|RF06|Filtrar productos|Funcional|Alta|
|RF07|Mostrar detalle del producto|Funcional|Alta|
|RF08|Agregar productos al carrito|Funcional|Alta|
|RF09|Modificar cantidades del carrito|Funcional|Alta|
|RF10|Eliminar productos del carrito|Funcional|Alta|
|RF11|Registrar pedidos|Funcional|Alta|
|RF12|Consultar historial de pedidos|Funcional|Alta|
|RF13|Realizar proceso de pago|Funcional|Alta|
|RF14|Registrar reseñas y calificaciones|Funcional|Media|
|RF15|Administrar lista de deseos|Funcional|Media|
|RF16|Administrar productos|Funcional|Alta|
|RF17|Administrar categorías|Funcional|Alta|
|RF18|Administrar usuarios|Funcional|Alta|
|RF19<br>RF20|Consultar información mediante API<br>Enviar confirmación del pedido|Funcional<br>Funcional|Alta<br>Media|



### **3. Requerimientos funcionales** 

Los requerimientos funcionales describen **qué debe hacer el sistema** . 

### **RF01. Registro de usuarios** 

El sistema deberá permitir que un usuario cree una cuenta proporcionando los datos solicitados, por ejemplo: 

- Nombre. 

- Apellido. 

- Correo electrónico. 

- Contraseña. 

- Teléfono. 

- Dirección. 

El formulario deberá utilizar controles adecuados para cada tipo de información. 

### **RF02. Inicio de sesión** 

El usuario podrá ingresar al sistema mediante su correo electrónico y contraseña. El sistema deberá validar las credenciales antes de permitir el acceso. 

### **RF03. Catálogo de productos** 

El sistema deberá mostrar los productos disponibles mediante tarjetas que incluyan información como: 

- Imagen. 

- Nombre. 

- Categoría. 

- Precio. 

- Disponibilidad. 

- Botón para visualizar detalles. 

- Botón para agregar al carrito. 

- 

### **RF04. Búsqueda y filtros** 

El usuario podrá localizar productos mediante un campo de búsqueda. 

También podrá utilizar filtros como: 

- Categoría. 

- Rango de precios. 

- Popularidad. 

- Disponibilidad. 

- 

### **RF05. Carrito de compras** 

El usuario podrá: 

- Agregar productos. 

- Modificar cantidades. 

- Eliminar productos. 

- Consultar subtotal. 

- Consultar total. 

- Continuar con el proceso de compra. 

- 

### **RF06. Gestión de pedidos** 

El sistema deberá registrar cada compra y permitir consultar: 

- Número de pedido. 

- Fecha. 

- Productos. 

- Cantidades. 

- Total. 

- Estado del pedido. 

### **RF07. Reseñas** 

Los usuarios podrán registrar una calificación y comentario sobre los productos adquiridos. 

### **RF08. Lista de deseos** 

El usuario podrá guardar productos que desea consultar o comprar posteriormente. 

### **RF09. Administración** 

El administrador podrá realizar operaciones CRUD: 

**C** reate → Crear **R** ead → Consultar **U** pdate → Actualizar **D** elete → Eliminar 

sobre los principales elementos del sistema. 

### **4. Requerimientos no funcionales** 

Los requerimientos no funcionales describen **cómo debe funcionar el sistema** . 

### **4.1 Seguridad** 

La aplicación deberá: 

- Utilizar contraseñas almacenadas mediante funciones de hash. 

- Validar los datos ingresados por el usuario. 

- Utilizar consultas preparadas para reducir riesgos de inyección SQL. 

- Controlar el acceso mediante sesiones. 

- Aplicar permisos según el tipo de usuario. 

- Utilizar HTTPS en producción. 

- Evitar mostrar información sensible en el código del cliente. 

### **4.2 Usabilidad** 

La aplicación deberá ser: 

- Intuitiva. 

- Fácil de utilizar. 

- Organizada. 

- Compatible con dispositivos móviles. 

- Clara en la presentación de mensajes y errores. 

### **4.3 Rendimiento** 

El sistema deberá responder rápidamente ante las operaciones habituales. 

Se deberán considerar aspectos como: 

- Optimización de imágenes. 

- Consultas eficientes a la base de datos. 

- Carga adecuada de recursos. 

- Uso apropiado de JavaScript. 

- Reducción de archivos innecesarios. 

### **4.4 Diseño** 

La interfaz deberá presentar: 

- Diseño responsivo. 

- Distribución clara de contenidos. 

- Contraste adecuado. 

- Tipografía legible. 

- Botones claramente identificables. 

- Navegación consistente. 

- Tarjetas para presentar productos. 

- Formularios organizados. 

### **5. Controles utilizados** 

Se utilizarán controles HTML adecuados de acuerdo con el tipo de información. 

|**Información**|**Control HTML**|
|---|---|
|Nombre|input type="text"|
|Correo|input type="email"|
|Contraseña|input type="password"|
|Teléfono|input type="tel"|
|Precio|input type="number"|
|Fecha|input type="date"|
|Categoría|select|
|Descripción|textarea|
|Imagen|input type="file"|
|Búsqueda|input type="search"|
|Calificación|input type="number" o controles de estrellas|
|Confirmacione|s button|
|Navegación|nav, enlaces y botones|



Esto permite que los formularios sean más claros y que el navegador pueda realizar validaciones básicas. 

### **6. Wireframes y Mockups** 

Antes de desarrollar la interfaz se realizarán **wireframes** para representar la estructura de las principales pantallas. 

Se pueden considerar los siguientes: 

Wireframe 1. Página principal Wireframe 2. Catálogo Wireframe 3. Carrito Wireframe 4. Administración 

Los **mockups** posteriormente podrán incorporar colores, tipografías, imágenes, iconos y otros elementos visuales. 

### **7. Diseño de la base de datos** 

### **7.1 Modelo relacional** 

La aplicación utilizará una base de datos relacional, por ejemplo **MySQL** . 

Una propuesta de estructura es: 

### **usuarios** 

- id_usuario — PK 

- nombre 

- apellido 

- correo 

- password 

- telefono 

- direccion 

- tipo_usuario 

### **categorias** 

- id_categoria — PK 

- nombre 

- descripcion 

### **productos** 

- id_producto — PK 

- id_categoria — FK 

- nombre 

- descripcion 

- precio 

- cantidad 

- imagen 

- estado 

### **pedidos** 

- id_pedido — PK 

- id_usuario — FK 

- • fecha 

- total 

- estado 

### **detalle_pedido** 

- id_detalle — PK 

- id_pedido — FK 

- id_producto — FK 

- cantidad 

- precio 

### **resenas** 

- id_resena — PK 

- id_usuario — FK 

- id_producto — FK 

- calificacion 

- comentario 

- fecha 

### **wishlist** 

- id_wishlist — PK 

- id_usuario — FK 

- id_producto — FK 

### **Crear las relaciones principales** 

### **8. Tecnologías utilizadas** 

### **Frontend** 

### **HTML5** 

Se utilizará para estructurar las páginas web mediante elementos semánticos como: 

- header 

- nav 

- main 

- section 

- article 

- footer 

- Formularios 

- Tablas 

- Botones 

### **CSS3** 

Se utilizará para: 

- Diseño visual. 

- Colores. 

- Tipografías. 

- Flexbox. 

- CSS Grid. 

- Media Queries. 

- Diseño responsivo. 

- Animaciones básicas. 

### **JavaScript** 

Se utilizará para implementar la interacción del usuario, por ejemplo: 

- Validaciones. 

- Eventos. 

- Manipulación del DOM. 

- Búsqueda. 

- Filtros. 

- Carrito. 

- Consumo de API. 

- Solicitudes fetch(). 

- Procesamiento de datos JSON. 

### **Backend** 

### **PHP** 

PHP será responsable de: 

- Procesar solicitudes. 

- Realizar operaciones CRUD. 

- Gestionar sesiones. 

- Validar información. 

- Aplicar reglas de negocio. 

- Comunicarse con la base de datos. 

- Proporcionar los servicios de la API. 

### **Base de datos** 

### **MySQL** 

Se utilizará para almacenar: 

- Usuarios. 

- Productos. 

- Categorías. 

- Pedidos. 

- Detalles de pedidos. 

- Reseñas. 

- Lista de deseos. 

### **Conexión** 

Se recomienda utilizar **PDO** para la comunicación entre PHP y MySQL, utilizando consultas preparadas. 

### **Servidor** 

Durante el desarrollo se puede utilizar: 

**XAMPP → Apache + PHP + MySQL** 

### **10. Arquitectura MVC** 

El proyecto utilizará el patrón **Modelo-Vista-Controlador (MVC)** . 

USUARIO │ 

▼ VISTA │ ▼ CONTROLADOR │ 

▼ MODELO ▼ BASE DE DATOS │ ▼ RESPUESTA │ ▼ VISTA │ ▼ USUARIO 

### **Modelo** 

Se encargará de trabajar con los datos y la base de datos. 

Ejemplo: 

Producto.php 

Usuario.php 

Pedido.php 

Categoria.php 

### **Vista** 

Contendrá la interfaz que utilizará el usuario. 

Ejemplo: 

index.php 

productos.php 

login.php 

carrito.php 

admin.php 

### **Controlador** 

Recibirá las solicitudes y coordinará las operaciones. 

Ejemplo: 

ProductoController.php 

UsuarioController.php 

PedidoController.php 

### **12. Configuración básica** 

### **URL Base** 

Durante el desarrollo con XAMPP: 

http://localhost/tienda-online/ 

La URL de la API podría ser: 

http://localhost/tienda-online/api/ 

Por ejemplo: 

http://localhost/tienda-online/api/productos.php 

### **Configuración de base de datos** 

El archivo conexion.php tendrá la información necesaria para establecer la conexión con MySQL. 

Conceptualmente: 

Servidor: localhost 

Base de datos: tienda_online 

Usuario: root 

Contraseña: configuración local 

En producción estos datos deberán almacenarse mediante una configuración segura y no exponerse directamente al usuario. 

### **13. API** 

La aplicación incluirá una API que permitirá la comunicación entre el frontend y el backend. 

Por ejemplo: 

|**Método**|**Endpoint**|**Operación**|
|---|---|---|
|GET|/api/productos|Consultar productos|
|GET|/api/productos/{id}|Consultar producto|
|POST|/api/productos|Crear producto|
|PUT|/api/productos/{id}|Actualizar producto|
|DELETE|/api/productos/{id}|Eliminar producto|
|GET|/api/categorias|Consultar categorías|
|POST|/api/pedidos|Crear pedido|



La información será intercambiada utilizando principalmente **JSON** . 

Ejemplo de respuesta: { "id_producto": 1, "nombre": "Producto A", "precio": 150, "cantidad": 20 } 

### **15. Funcionalidades que deberán demostrarse** 

Durante la presentación del proyecto se puede realizar una demostración siguiendo este orden: 

### **15.1 Registro** 

Demostrar cómo un usuario: 

   1. Ingresa al formulario. 

   2. Introduce sus datos. 

   3. Envía el formulario. 

4. Se registra en la base de datos. 

**15.2 Inicio de sesión** 

Demostrar: 

1. Introducción del correo. 

2. Introducción de contraseña. 

3. Validación. 

4. Creación de sesión. 

5. Acceso al sistema. 

**15.3 Consulta de productos** 

Demostrar: 

1. Catálogo. 

2. Búsqueda. 

3. Filtros. 

4. Detalle del producto. 

### **15.4 CRUD de productos** 

Demostrar las cuatro operaciones: 

1. INSERT  → Agregar 

2. SELECT  → Consultar 

3. UPDATE  → Actualizar 

4. DELETE  → Eliminar 

Por ejemplo: 

Agregar producto ↓ Base de datos ↓ Consultar producto ↓ Modificar producto ↓ Actualizar BD ↓ Eliminar producto ↓ Base de datos **15.5 Carrito** 

Demostrar: 

- Agregar. 

- Incrementar cantidad. 

- Disminuir cantidad. 

- Eliminar. 

- Calcular subtotal. 

- Calcular total. 

### **15.6 Pedido** 

Demostrar cómo el carrito genera un pedido y cómo este queda almacenado en: pedidos │ └── detalle_pedido 

### **16. Explicación de la interacción entre aplicación y base de datos** 

El flujo principal será: 

USUARIO │ ▼ INTERFAZ WEB HTML + CSS + JS │ ▼ FETCH / HTTP │ ▼ API PHP │ ▼ CONTROLADOR │ ▼ MODELO │ ▼ PDO │ ▼ MYSQL 

Cuando el usuario, por ejemplo, agrega un producto: 

Usuario ↓ Selecciona producto ↓ JavaScript ↓ fetch() ↓ API PHP ↓ Controlador ↓ Modelo ↓ PDO ↓ MySQL ↓ Respuesta JSON ↓ JavaScript ↓ 

Actualización de la interfaz 

Esto permite explicar claramente **cómo interactúan el frontend, backend, API y base de datos** . 

### **17. Explicación del código fuente** 

Para la exposición no es necesario explicar absolutamente cada línea. Conviene seleccionar los componentes principales. 

### **Frontend** 

Explicar: 

- Estructura HTML5. 

- Formularios. 

- CSS responsivo. 

- Eventos JavaScript. 

- fetch(). 

- JSON. 

- Manipulación del DOM. 

### **Backend** 

Explicar: 

PHP. Sesiones. Validaciones. PDO. Consultas preparadas. CRUD. Respuestas JSON. **Base de datos** 

Explicar: 

   - Llaves primarias. 

   - Llaves foráneas. 

   - Relaciones. 

   - Integridad de datos. 

- Consultas SQL. 

- **API** 

Explicar: 

- Métodos HTTP. 

- Endpoints. 

- Parámetros. 

- JSON. 

- Respuestas. 

- Códigos de estado HTTP. 

### **18. Flujo general del sistema** 

Finalmente, puedes presentar este esquema como la integración de todo el proyecto: 



<!-- Start of picture text -->
                         TIENDA ONLINE<br>                              │<br>              ┌───────────────┴───────────────┐<br>              │                               │<br>           CLIENTE                        ADMINISTRADOR<br>              │                               │<br>              ▼                               ▼<br>        ┌───────────┐                  ┌──────────────┐<br>        │ FRONTEND         │                  │ PANEL ADMIN            │<br>        │ HTML/CSS/        │                  │                                          │<br>        │    JS                         │                  │ CRUD                             │<br>        └─────┬─────┘                  └──────┬───────┘<br>              │                               │<br>              └──────────────┬────────────────┘<br>                             ▼<br>                         ┌───────┐<br>                         │  API            │<br>                         │  PHP   │<br>                         └───┬───┘<br>                             ▼<br>                          ┌─────┐<br>                          │ MVC     │<br>                          └──┬──┘<br>                    ▼<br>                          ┌─────┐<br>                          │ PDO     │<br>                          └──┬──┘<br>                                  ▼<br>                       ┌───────────┐<br>                       │   MYSQL               │<br>                       └───────────┘<br><!-- End of picture text -->

### **19. Resultado esperado** 

Al finalizar el proyecto se contará con una aplicación web de comercio electrónico capaz de integrar **interfaz de usuario, lógica de negocio, API y base de datos** , aplicando una arquitectura organizada mediante MVC. 

La solución permitirá demostrar procesos completos de desarrollo web, incluyendo **diseño de interfaces, formularios, validaciones, autenticación, CRUD, consumo de API, manejo de sesiones, consultas a bases de datos, relaciones entre tablas y diseño responsivo** . 

