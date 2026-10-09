# Doméstik - Tienda en Línea de Electrodomésticos

Plataforma de comercio electrónico (E-commerce) desarrollada en **PHP** y **MySQL / MariaDB**, diseñada para la venta de electrodomésticos, refrigeración, lavado, cocción y climatización para el hogar.

---

## Características Principales

- **Catálogo Interactivo:** Búsqueda rápida y filtros asíncronos por categoría, marca y precio.
- **Carrito de Compras y Checkout:** Proceso de compra con validación de stock y cálculo de impuestos.
- **Facturación Electrónica:** Generación de recibo/factura digital y seguimiento del pedido.
- **Lista de Deseos (Wishlist):** Guardado de productos favoritos por usuario.
- **Panel de Administración:** Gestión completa (CRUD) de productos, categorías, pedidos, usuarios y diagnóstico de APIs.
- **Notificaciones por Correo:** Envío automático de confirmaciones de compra y recuperación de contraseña vía **PHPMailer (SMTP)**.
- **Diseño Responsivo:** Adaptado para teléfonos móviles, tablets y computadoras de escritorio.

---

## Tecnologías Utilizadas

- **Backend:** PHP 8.0+ (Arquitectura MVC y APIs RESTful en JSON)
- **Base de Datos:** MySQL / MariaDB (Motor transaccional InnoDB)
- **Frontend:** HTML5 semántico, CSS3, JavaScript Vanilla (Fetch API) y Bootstrap 5.3
- **Librerías:** PHPMailer (Gestión y envío de correos electrónicos)

---

## Requisitos Previos

- Servidor local como **XAMPP**, **WampServer** o **Laragon** (con PHP 8.0 o superior y MySQL).
- Navegador web moderno (Chrome, Edge, Firefox).

---

## Instalación y Puesta en Marcha

### 1. Clonar o copiar el proyecto
Coloca la carpeta del proyecto dentro del directorio raíz de tu servidor web:
- En XAMPP: `C:/xampp/htdocs/Proyecto_Tienda_online`

### 2. Crear e importar la base de datos
1. Abre **phpMyAdmin** (`http://localhost/phpmyadmin`).
2. Crea una nueva base de datos llamada `tienda_electrodomesticos`.
3. Importa los archivos SQL ubicados en la carpeta `database/` en el siguiente orden:
   1. `database/schema.sql` (Tablas y estructura)
   2. `database/procedures.sql` (Procedimientos almacenados)
   3. `database/seeds.sql` (Datos iniciales de prueba)

### 3. Configurar variables de entorno
Crea un archivo llamado `.env` en la raíz del proyecto copiando el archivo de ejemplo `.env.example`:

```ini
DB_HOST=localhost
DB_NAME=tienda_electrodomesticos
DB_USER=root
DB_PASS=

# Configuración SMTP para envío de correos
SMTP_HOST=smtp.gmail.com
SMTP_USER=tu_correo@gmail.com
SMTP_PASS=tu_contraseña_de_aplicacion
SMTP_PORT=587
SMTP_SECURE=tls
```

### 4. Acceder al sistema
Abre tu navegador e ingresa a:
`http://localhost/Proyecto_Tienda_online`

---

## Cuentas de Prueba

| Rol | Correo | Contraseña |
| :--- | :--- | :--- |
| **Administrador** | `admin@domestik.gt` | `Admin123!` |
| **Cliente** | `cliente@domestik.gt` | `Cliente123!` |

---

## Estructura del Proyecto

```text
├── api/            # Endpoints RESTful para peticiones asíncronas
├── app/
│   ├── controllers/# Controladores del sistema (Lógica de negocio)
│   ├── libs/       # Librería PHPMailer integrada
│   ├── middlewares/# Control de acceso y sesiones
│   ├── models/     # Modelos y consultas a la base de datos
│   └── services/   # Servicio de envío de correos
├── config/         # Configuración general y conexión PDO
├── database/       # Scripts SQL (Esquema, Procedimientos y Datos)
├── public/         # Archivos públicos (CSS, JS, Imágenes)
├── views/          # Vistas de la aplicación (Frontend y Admin)
├── .env.example    # Plantilla de variables de entorno
├── index.php       # Enrutador principal de la aplicación
└── README.md       # Documentación del proyecto
```

---

## Autor

Proyecto desarrollado como parte de la formación técnica en Desarrollo Web - INTECAP.
