<?php
// db_connect.php
// Definir las credenciales de la base de datos
$servername = "localhost";
$username = "root"; // TU USUARIO de la base de datos (comúnmente 'root')
$password = "";     // TU CONTRASEÑA de la base de datos (a menudo vacía por defecto)
$dbname = "gaming";

// Crear la conexión usando el estilo orientado a objetos de MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);

// Establecer el charset a UTF8 para soportar tildes y caracteres especiales
$conn->set_charset("utf8mb4");

// Verificar si la conexión falló
if ($conn->connect_error) {
    // Detiene la ejecución de la página y muestra un error genérico
    die("Error de conexión. Por favor, inténtelo más tarde.");
}
?>