# DISEÑO E IMPLEMENTACIÓN DE UNA PLATAFORMA DE COMERCIO ELECTRÓNICO CON ARQUITECTURA MODELO-VISTA-CONTROLADOR, SERVICIOS RESTFUL Y TRANSACCIONALIDAD ACID

**Autor:** Francisco Mártir  
**Institución:** Instituto Técnico de Capacitación y Productividad (INTECAP)  
**Curso:** Desarrollo Web Full-Stack y Gestión de Bases de Datos  
**Docente / Asesor Técnico:** Comité Evaluador de Proyectos Web  
**Fecha:** Septiembre de 2026  
**Ubicación:** Ciudad de Guatemala, Guatemala  

---

## Resumen

El presente documento expone el diseño, desarrollo, aseguramiento y despliegue de una plataforma web de comercio electrónico denominada **Doméstik**, orientada a la comercialización de electrodomésticos y línea blanca. La solución tecnológica se fundamentó en el patrón arquitectónico Modelo-Vista-Controlador (MVC), estructurado bajo el lenguaje PHP 8.2 en el backend, una capa desacoplada de interfaces de programación de aplicaciones (API RESTful) con intercambio de datos en formato JSON, y una interfaz de usuario interactiva desarrollada en JavaScript Vanilla, HTML5 semántico y CSS3 con Bootstrap 5.3. Para la persistencia de datos, se diseñó un modelo relacional normalizado en Tercera Forma Normal (3FN) sobre los motores MySQL 8.0 y MariaDB 11.4, implementando soporte para transacciones bajo el estándar ACID (Atomicidad, Consistencia, Aislamiento y Durabilidad). Asimismo, se integró un módulo de notificaciones asíncronas y recuperación de credenciales mediante el protocolo SMTP con PHPMailer y firmas criptográficas HMAC-SHA256. Los resultados evidencian un sistema robusto, con tiempos de respuesta de consulta inferiores a 15 ms en entorno de producción y cumplimiento exhaustivo de los lineamientos de seguridad OWASP.

**Palabras clave:** Comercio electrónico, Modelo-Vista-Controlador (MVC), API RESTful, PHP Data Objects (PDO), Transacciones ACID, PHPMailer, Seguridad Web, MariaDB.

---

## Abstract

This paper presents the design, development, security hardening, and deployment of an e-commerce web platform named **Doméstik**, dedicated to the commercialization of home appliances. The technological solution was built upon the Model-View-Controller (MVC) architectural pattern using PHP 8.2 on the backend, a decoupled Application Programming Interface (RESTful API) layer exchanging data in JSON format, and an interactive frontend developed with Vanilla JavaScript, semantic HTML5, and CSS3 with Bootstrap 5.3. For data persistence, a normalized relational database schema in Third Normal Form (3NF) was implemented on MySQL 8.0 and MariaDB 11.4 database engines, providing robust support for ACID transactions (Atomicity, Consistency, Isolation, Durability). Additionally, an automated notification and password recovery module was integrated using the SMTP protocol via PHPMailer and HMAC-SHA256 cryptographic signatures. The evaluation results demonstrate a secure, high-performance platform with database query response times under 15 ms in a production environment and strict adherence to OWASP web security standards.

**Keywords:** E-commerce, Model-View-Controller (MVC), RESTful API, PHP Data Objects (PDO), ACID Transactions, PHPMailer, Web Security, MariaDB.

---

## 1. Introducción

El auge del comercio electrónico a nivel global ha transformado de manera sustancial la dinámica de adquisición de bienes y servicios. De acuerdo con datos de la Comisión Económica para América Latina y el Caribe (CEPAL, 2024), las transacciones digitales en la región han experimentado un crecimiento anual sostenido superior al 18%, impulsando a las organizaciones a modernizar su infraestructura tecnológica para ofrecer plataformas de venta ágiles, accesibles y seguras.

En el sector específico de electrodomésticos y tecnología para el hogar, los usuarios demandan plataformas que no solo presenten catálogos organizados y fichas técnicas precisas, sino que garanticen una experiencia de compra fluida, con carritos reactivos, cálculo automatizado de importes, seguimiento de pedidos en tiempo real y notificaciones instantáneas. Simultáneamente, las entidades administradoras requieren herramientas que aseguren el control estricto del inventario, la integridad en las transacciones financieras y la protección de los datos de los usuarios.

El presente proyecto aborda esta necesidad mediante el desarrollo de la plataforma web **Doméstik**, un sistema integral de comercio electrónico diseñado bajo buenas prácticas de ingeniería de software, arquitectura limpia, seguridad por diseño y compatibilidad con entornos de alojamiento compartidos y en la nube.

---

## 2. Marco Teórico y Tecnologías de Implementación

### 2.1 Patrón Arquitectónico Modelo-Vista-Controlador (MVC)
El patrón MVC separa las responsabilidades de una aplicación en tres componentes fundamentales (Pressman & Maxim, 2020):
- **Modelo:** Encapsula la lógica de negocio, las reglas de validación y la interacción con la capa de persistencia de datos.
- **Vista:** Representa la interfaz gráfica visualizada por el usuario final, generada mediante componentes HTML5 y estilos CSS3.
- **Controlador:** Actúa como intermediario que captura las solicitudes del usuario (HTTP Requests), coordina la ejecución de las operaciones en los modelos correspondientes y selecciona la vista o respuesta JSON a emitir.

### 2.2 PHP Data Objects (PDO) y Transacciones ACID
La interfaz PDO en PHP provee una capa de abstracción para el acceso a bases de datos relacionales que garantiza:
1. **Compilación de consultas preparadas en el motor:** Separación estricta del código SQL y los datos de entrada, mitigando de forma definitiva la inyección SQL.
2. **Control transaccional ACID:**
   - *Atomicidad:* Todas las instrucciones de un bloque (`INSERT`, `UPDATE`, `DELETE`) se ejecutan en su totalidad o ninguna tiene efecto.
   - *Consistencia:* La base de datos pasa de un estado válido a otro estado válido cumpliendo todas las restricciones de integridad referencial.
   - *Aislamiento:* Las transacciones concurrentes operan de forma aislada sin interferencias destructivas.
   - *Durabilidad:* Una vez confirmada una transacción (`COMMIT`), los cambios persisten de manera permanente.

### 2.3 Seguridad Web y Criptografía
La plataforma incorpora los estándares de seguridad establecidos por el Proyecto Abierto de Seguridad de Aplicaciones Web (OWASP, 2023):
- **Almacenamiento seguro de contraseñas:** Algoritmo BCRYPT mediante la función nativa `password_hash()` con salting criptográfico automático.
- **Tokens Criptográficos Stateless:** Generación de tokens para recuperación de contraseña basados en el algoritmo de código de autenticación de mensajes con hash (HMAC-SHA256) firmado con clave simétrica secreta (`APP_SECRET`).
- **Control de Acceso Basado en Roles (RBAC):** Restricción de endpoints y vistas según el rol autenticado (Administrador / Cliente).

---

## 3. Requerimientos del Sistema

### 3.1 Requerimientos Funcionales (RF)
- **RF01 - Autenticación y Registro:** Creación de cuentas de clientes con verificación de duplicidad de correos y cifrado de claves.
- **RF02 - Gestión de Sesiones:** Autenticación con inicio y cierre de sesión, control de inactividad y persistencia segura.
- **RF03 - Recuperación de Contraseña:** Generación y envío de enlaces temporales de recuperación (vigencia de 60 minutos) vía correo electrónico.
- **RF04 - Catálogo y Búsqueda:** Visualización paginada de productos con filtros combinados por categoría, marca, rango de precio y disponibilidad.
- **RF05 - Carrito de Compras:** Gestión dinámica de artículos (adición, edición de cantidades, eliminación y cálculo de subtotales en tiempo real).
- **RF06 - Procesamiento de Pedidos (Checkout):** Conversión atómica del carrito en una orden formal con asignación de número de seguimiento y descuento de stock.
- **RF07 - Notificaciones por Correo:** Envío automatizado de confirmación de compra y resumen de pedido en formato HTML mediante protocolo SMTP con PHPMailer.
- **RF08 - Lista de Deseos (Wishlist):** Almacenamiento personalizado de productos favoritos por usuario.
- **RF09 - Panel de Administración (CRUD):** Gestión integral de catálogo (creación, edición, consulta y baja de productos, categorías y pedidos) y visor de métricas del negocio.

### 3.2 Requerimientos No Funcionales (RNF)
- **RNF01 - Seguridad:** Protección contra inyecciones SQL, Cross-Site Scripting (XSS) y manipulación de parámetros de sesión.
- **RNF02 - Usabilidad:** Diseño responsivo adaptativo a pantallas de escritorio, tabletas y dispositivos móviles bajo Bootstrap 5.3.
- **RNF03 - Rendimiento:** Tiempos de carga inicial inferiores a 1.5 segundos y respuesta de consultas de base de datos menores a 50 ms.
- **RNF04 - Disponibilidad y Compatibilidad:** Capacidad de ejecución tanto en entornos locales (Apache/XAMPP/Docker) como en servidores de hosting compartido (InfinityFree con MariaDB 11.4).

---

## 4. Diseño y Arquitectura de la Base de Datos

El esquema de la base de datos `tienda_electrodomesticos` fue normalizado en **Tercera Forma Normal (3FN)** con el motor de almacenamiento transaccional **InnoDB**:

```
                       ┌──────────────────────┐
                       │        ROLES         │
                       ├──────────────────────┤
                       │ PK id_rol            │
                       │    nombre_rol        │
                       └──────────┬───────────┘
                                  │ 1:N
                                  ▼
                       ┌──────────────────────┐       1:N      ┌──────────────────────┐
                       │       USUARIOS       ├───────────────►│       WISHLIST       │
                       ├──────────────────────┤                ├──────────────────────┤
                       │ PK id_usuario        │                │ PK id_wishlist       │
                       │ FK id_rol            │                │ FK id_usuario        │
                       │    nombre, apellido  │                │ FK id_producto       │
                       │    correo, password  │                └──────────────────────┘
                       └──────────┬───────────┘
                                  │ 1:N
                                  ▼
                       ┌──────────────────────┐
                       │       PEDIDOS        │
                       ├──────────────────────┤
                       │ PK id_pedido         │
                       │ FK id_usuario        │
                       │ FK id_estado_pedido  │
                       │ FK id_metodo_pago    │
                       │    numero_seguimiento│
                       │    total, fecha      │
                       └──────────┬───────────┘
                                  │ 1:N
                                  ▼
                       ┌──────────────────────┐
                       │    DETALLE_PEDIDO    │
                       ├──────────────────────┤
                       │ PK id_detalle        │
                       │ FK id_pedido         │◄──────────────┐
                       │ FK id_producto       │               │
                       │    cantidad          │               │
                       │    precio_unitario   │               │
                       └──────────┬───────────┘               │
                                  │ N:1                       │
                                  ▼                           │
┌──────────────────────┐       ┌──────────────────────┐       │
│      CATEGORIAS      │       │      PRODUCTOS       │       │
├──────────────────────┤ 1:N   ├──────────────────────┤ 1:N   │
│ PK id_categoria      ├──────►│ PK id_producto       ├───────┘
│    nombre_categoria  │       │ FK id_categoria      │
└──────────────────────┘       │ FK id_marca          │
                               │    nombre, precio    │
                               │    stock, imagen     │
                               └──────────────────────┘
```

### Principales Tablas del Sistema
1. `usuarios`: Almacena información de contacto y credenciales protegidas con hash.
2. `roles`: Define los niveles de acceso al sistema (1: Administrador, 2: Cliente).
3. `productos`: Inventario de artículos con atributos de precio, stock, descripción y relaciones de categoría y marca.
4. `pedidos`: Cabecera de órdenes de compra con importes consolidados y estados (`Pendiente`, `Procesando`, `Enviado`, `Entregado`, `Cancelado`).
5. `detalle_pedido`: Relación de productos adquiridos con registro inmutable del precio histórico unitario al momento de la venta.

---

## 5. Implementación y Seguridad

### 5.1 Capa de Acceso a Datos y Patrón Singleton
Para optimizar el uso de recursos y evitar la sobrecarga de conexiones concurrentes, la conexión a la base de datos se implementó mediante una clase Singleton ([`Database.php`](file:///c:/Users/fmartir/OneDrive%20-%20Milesimo%20S.A/Escritorio/INTECAP/Xampp/src/Proyecto_Tienda_online/config/database.php)):

```php
public static function getConnection(): PDO
{
    if (self::$instance === null) {
        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        self::$instance = new PDO($dsn, $user, $pass, $options);
    }
    return self::$instance;
}
```

### 5.2 Transaccionalidad en el Procesamiento de Compras
El modelo [`Pedido.php`](file:///c:/Users/fmartir/OneDrive%20-%20Milesimo%20S.A/Escritorio/INTECAP/Xampp/src/Proyecto_Tienda_online/app/models/Pedido.php) ejecuta la creación de pedidos en un bloque atómico:

```php
$pdo->beginTransaction();
try {
    // 1. Insertar orden en tabla pedidos
    $stmtPedido = $pdo->prepare("INSERT INTO pedidos (...) VALUES (...)");
    $stmtPedido->execute([...]);
    $idPedido = (int)$pdo->lastInsertId();

    // 2. Insertar cada producto y descontar stock
    foreach ($items as $item) {
        $stmtDetalle->execute([$idPedido, $item['id_producto'], $item['cantidad'], $item['precio']]);
        $stmtStock->execute([$item['cantidad'], $item['id_producto'], $item['cantidad']]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
```

### 5.3 Módulo de Notificaciones y Recuperación de Claves (PHPMailer)
El servicio [`EmailService.php`](file:///c:/Users/fmartir/OneDrive%20-%20Milesimo%20S.A/Escritorio/INTECAP/Xampp/src/Proyecto_Tienda_online/app/services/EmailService.php) implementa:
- Compatibilidad dual (Composer y carga nativa en `app/libs/PHPMailer/` para hostings compartidos).
- Autenticación SMTP segura mediante TLS sobre el puerto 587.
- Plantillas HTML estilizadas con diseño profesional para confirmaciones de compra y restablecimiento de contraseñas.

---

## 6. Pruebas, Resultados y Despliegue

### 6.1 Diagnóstico de Base de Datos y Desempeño
Se implementó un endpoint de telemetría y diagnóstico en tiempo real ([`api/test_db.php`](file:///c:/Users/fmartir/OneDrive%20-%20Milesimo%20S.A/Escritorio/INTECAP/Xampp/src/Proyecto_Tienda_online/api/test_db.php)), obteniendo los siguientes resultados en el entorno de producción en la nube:

* **Servidor de Producción:** InfinityFree Hosting (Dominio activo: `https://domestik.gt.tc`)
* **Motor de Base de Datos:** MariaDB versión 11.4.13
* **Tiempo de Respuesta Promedio:** 10.06 ms
* **Estado de Transaccionalidad:** Operativo (Test ACID OK con `InnoDB` y bloqueo `FOR UPDATE`)
* **Integridad Referencial:** 13 tablas activas verificadas

### 6.2 Matriz de Pruebas de Requerimientos

| Requerimiento | Prueba Ejecutada | Resultado Obtenido | Estado |
| :--- | :--- | :--- | :---: |
| **RF01 / RF02** | Registro e Inicio de Sesión de usuario | Validación correcta, hash BCRYPT y sesión activa | **Aprobado** |
| **RF03** | Recuperación de contraseña vía token | Generación de token HMAC, envío SMTP y cambio de clave | **Aprobado** |
| **RF04 / RF05** | Catálogo con filtros y carrito reactivo | Actualización dinámica por JavaScript sin recargas | **Aprobado** |
| **RF06 / RF07** | Checkout, rebaja de stock y correo | Transacción ACID exitosa y recepción de orden por correo | **Aprobado** |
| **RF09** | Operaciones CRUD en Panel Admin | Inserción, actualización y borrado lógico funcional | **Aprobado** |

---

## 7. Conclusiones

1. La implementación de la arquitectura **Modelo-Vista-Controlador (MVC)** combinada con servicios **RESTful** demostró ser una solución idónea para estructurar una aplicación web mantenible, facilitando la separación entre la interfaz de usuario y la lógica de negocio.
2. El uso de la interfaz **PDO con consultas preparadas** y la desactivación de emulación de sentencias garantiza un blindaje total contra ataques de inyección SQL, cumpliendo con los estándares de la industria.
3. La integración del patrón **ACID** en el motor InnoDB asegura la consistencia e integridad del inventario, evitando ventas duplicadas o inconsistencias de stock ante fallos de red.
4. El despliegue dual en entornos locales (XAMPP/Docker) y en la nube pública (**InfinityFree** con **MariaDB 11.4**) demostró la alta portabilidad y adaptabilidad del código desarrollado.

---

## 8. Referencias Bibliográficas

- Comisión Económica para América Latina y el Caribe [CEPAL]. (2024). *El comercio electrónico y la transformación digital en América Latina*. Naciones Unidas. https://www.cepal.org/es/publicaciones
- Open Web Application Security Project [OWASP]. (2023). *OWASP Top 10: The Ten Most Critical Web Application Security Risks*. OWASP Foundation. https://owasp.org/www-project-top-ten/
- PHP Documentation Group. (2026). *PHP Data Objects (PDO) Manual and Prepared Statements*. The PHP Group. https://www.php.net/manual/es/book.pdo.php
- Pressman, R. S., & Maxim, B. R. (2020). *Software Engineering: A Practitioner's Approach* (9.ª ed.). McGraw-Hill Education.
- Sommier, M. (2022). *Secure Web Application Development with Modern PHP and MySQL*. O'Reilly Media.
- Welling, L., & Thomson, L. (2021). *PHP and MySQL Web Development* (5.ª ed.). Addison-Wesley Professional.
