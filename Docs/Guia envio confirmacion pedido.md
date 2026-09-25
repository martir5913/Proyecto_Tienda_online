## **Guía práctica: Confirmación de pedidos por correo electrónico** 

# 1. Objetivo del ejemplo 

Implementar una funcionalidad para que, al presionar el botón “Realizar pedido”, el sitio web envíe los datos del pedido a PHP, registre la compra en la base de datos y envíe automáticamente un correo de confirmación al cliente. 

# 2. Flujo de funcionamiento 

- El cliente revisa su carrito y presiona “Realizar pedido”. 

- JavaScript recopila los datos del cliente y los productos. 

- fetch() envía la información a un archivo PHP mediante una petición POST. 

- PHP valida los datos y registra el pedido en MySQL. 

- PHPMailer utiliza SMTP para enviar el correo de confirmación. 

- El cliente recibe un correo con el resumen de su pedido. 

# 3. Estructura recomendada del proyecto 

tienda/ ├── index.html ├── carrito.html ├── js/ │   └── pedido.js ├── php/ │   ├── conexion.php │   └── registrar_pedido.php └── vendor/ └── phpmailer/ 

# 4. Botón para realizar el pedido 

Archivo: carrito.html 

<button id="btnComprar"> Realizar pedido </button> 

El botón tiene el identificador btnComprar. JavaScript utilizará este identificador para detectar el clic del usuario y comenzar el proceso. 

# 5. JavaScript: enviar el pedido a PHP 

Archivo: js/pedido.js 

document.getElementById("btnComprar").addEventListener("click", async () => { 

const pedido = { cliente: "Juan Pérez", correo: "cliente@gmail.com", total: 250.00, productos: [ { nombre: "Producto 1", cantidad: 2, precio: 100 }, 

{ nombre: "Producto 2", cantidad: 1, precio: 50 } ] }; try { const respuesta = await fetch("../php/registrar_pedido.php", {  method: "POST",  headers: { "Content-Type": "application/json"  }, body: JSON.stringify(pedido) }); const resultado = await respuesta.json(); if (resultado.success) { 

alert("Pedido realizado correctamente. " +  "Se ha enviado la confirmación a tu correo."); } else { alert("Error: " + resultado.message); } } catch (error) { console.error(error); alert("No fue posible procesar el pedido."); } 

### }); 

Explicación: 

- addEventListener("click") detecta cuando el cliente presiona el botón. 

- pedido es un objeto JavaScript que contiene los datos que se enviarán. 

- fetch() realiza la comunicación entre JavaScript y PHP. 

- method: POST indica que los datos se enviarán al servidor. 

- JSON.stringify(pedido) convierte el objeto JavaScript a formato JSON. 

- respuesta.json() convierte la respuesta de PHP nuevamente a un objeto JavaScript. 

- success permite saber si el servidor procesó correctamente el pedido. 

# 6. Conexión con MySQL 

Archivo: php/conexion.php 

<?php 

$host = "localhost"; $db   = "tienda"; $user = "root"; $pass = ""; 

try { 

$conexion = new PDO( "mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass ); 

$conexion->setAttribute( PDO::ATTR_ERRMODE, 

PDO::ERRMODE_EXCEPTION ); 

### } catch (PDOException $e) { 

die("Error de conexión: " . $e->getMessage()); 

} 

PDO permite establecer la conexión con MySQL. En un proyecto real conviene utilizar variables de entorno o un archivo de configuración protegido para las credenciales. 

# 7. Tabla básica para pedidos 

Ejemplo de SQL: 

CREATE DATABASE tienda; USE tienda; 

CREATE TABLE pedidos ( id_pedido INT AUTO_INCREMENT PRIMARY KEY, cliente VARCHAR(100) NOT NULL, correo VARCHAR(150) NOT NULL, total DECIMAL(10,2) NOT NULL, fecha DATETIME DEFAULT CURRENT_TIMESTAMP ); 

# 8. PHP: recibir y registrar el pedido 

Archivo: php/registrar_pedido.php 

<?php 

header("Content-Type: application/json"); 

require_once "conexion.php"; require_once "../vendor/autoload.php"; 

use PHPMailer\PHPMailer\PHPMailer; use PHPMailer\PHPMailer\Exception; 

$data = json_decode( file_get_contents("php://input"), true ); if (!$data) { echo json_encode([ "success" => false, "message" => "Datos del pedido no válidos." ]); exit; } 

$cliente = trim($data["cliente"] ?? ""); $correo = trim($data["correo"] ?? ""); 

$total = (float)($data["total"] ?? 0); $productos = $data["productos"] ?? []; 

if ($cliente === "" || !filter_var($correo, FILTER_VALIDATE_EMAIL) || $total <= 0) { echo json_encode([ "success" => false, "message" => "Faltan datos válidos del pedido." ]); exit; } try { 

$sql = "INSERT INTO pedidos (cliente, correo, total) VALUES (?, ?, ?)"; $stmt = $conexion->prepare($sql); $stmt->execute([$cliente, $correo, $total]); $idPedido = $conexion->lastInsertId(); 

$detalle = ""; foreach ($productos as $producto) { $nombre = htmlspecialchars($producto["nombre"] ?? ""); $cantidad = (int)($producto["cantidad"] ?? 0); $precio = (float)($producto["precio"] ?? 0); 

$detalle .= "<tr> <td>$nombre</td> <td>$cantidad</td> <td>Q " . number_format($precio, 2) . "</td> </tr>"; } 

$mail = new PHPMailer(true); 

// Configuración SMTP $mail->isSMTP(); $mail->Host = "smtp.gmail.com"; $mail->SMTPAuth = true; $mail->Username = "TU_CORREO@gmail.com"; $mail->Password = "TU_CONTRASENA_DE_APLICACION"; $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; $mail->Port = 587; 

$mail->setFrom("TU_CORREO@gmail.com", "Tienda en Línea"); $mail->addAddress($correo, $cliente); 

$mail->isHTML(true); $mail->Subject = "Confirmación de pedido #".$idPedido; $mail->Body = " 

<h2>Confirmación de pedido</h2> <p>Hola <strong>$cliente</strong>,</p> <p>Hemos recibido correctamente tu pedido.</p> 

<p><strong>Número de pedido:</strong> #$idPedido</p> 

<table border='1' cellpadding='8'> <tr> <th>Producto</th> <th>Cantidad</th> <th>Precio</th> </tr> $detalle </table> 

<h3>Total: Q " . number_format($total, 2) . "</h3> <p>Gracias por tu compra.</p> "; 

$mail->send(); 

echo json_encode([ "success" => true, "message" => "Pedido registrado y correo enviado." ]); } catch (Exception $e) { 

echo json_encode([ "success" => false, "message" => "El pedido fue procesado, pero no se pudo enviar el correo." ]); } 

# 9. Explicación de la parte más importante 

json_decode() obtiene los datos enviados por JavaScript. prepare() y execute() permiten insertar los datos mediante una consulta preparada. lastInsertId() obtiene el número generado para el nuevo pedido. PHPMailer se encarga de construir y enviar el correo. smtp.gmail.com y el puerto 587 permiten utilizar SMTP con Gmail. addAddress() establece el correo del cliente que recibirá la confirmación. isHTML(true) permite utilizar HTML dentro del mensaje. send() realiza el envío del correo. 

# 10. Instalación de PHPMailer 

Desde la carpeta principal del proyecto se puede instalar PHPMailer utilizando Composer: 

composer require phpmailer/phpmailer 

Después de la instalación aparecerá la carpeta vendor y el archivo autoload.php. El código PHP utiliza require_once ../vendor/autoload.php para cargar la biblioteca. 

# 11. Configuración de Gmail 

Para una cuenta Gmail no se recomienda colocar la contraseña normal de la cuenta en el código. Se debe utilizar una contraseña de aplicación cuando la cuenta tenga habilitada la verificación en dos pasos. En el ejemplo, TU_CONTRASENA_DE_APLICACION representa ese valor. 

Importante: no publiques las credenciales SMTP en GitHub ni las incluyas directamente en un proyecto público. En una aplicación real es preferible utilizar variables de entorno. 

# 12. Ejemplo del correo recibido 

Asunto: Confirmación de pedido #25 

Confirmación de pedido Hola Juan Pérez, 

Hemos recibido correctamente tu pedido. Número de pedido: #25 

Producto 1 — 2 unidades — Q100.00 Producto 2 — 1 unidad — Q50.00 

Total: Q250.00 

Gracias por tu compra. 

# 13. Recomendaciones para integrarlo al proyecto existente 

No utilizar datos escritos manualmente como “Juan Pérez” o “cliente@gmail.com”; deben provenir del formulario, sesión o cuenta del cliente. 

El total debería calcularse nuevamente en PHP a partir de los productos almacenados o validados en el servidor. No se debe confiar únicamente en el total enviado por JavaScript. 

Registrar el pedido y sus productos en tablas relacionadas, por ejemplo pedidos y detalle_pedido. 

Validar existencia, precio y cantidad de cada producto antes de confirmar la compra. 

Utilizar consultas preparadas para evitar inyección SQL. 

Guardar las credenciales SMTP fuera del código fuente. 

Mostrar al cliente un número de pedido después de completar la compra. 

# 14. Resultado esperado 

Cuando el cliente presione “Realizar pedido”, el sistema deberá registrar la compra, generar un número de pedido y enviar automáticamente un correo al correo asociado al cliente. De esta manera, la funcionalidad queda integrada al proceso de compra de la tienda en línea. 

