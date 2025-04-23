<?php
// Configuración de la conexión a la base de datos
$host = 'localhost';       // Servidor de la base de datos 
$usuario = 'root';             // Usuario de la base de datos
$contrasena = '';              // Contraseña del usuario 
$base_de_datos = 'bakersoft'; // Nombre de la base de datos


 /* Con esto establece la conexión con la base de datos MySQL
  la variable $conn para mantener relacion con los otros archivos*/

$conn = new mysqli($host, $usuario, $contrasena, $base_de_datos);

// Con esto verificación la conexión 

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

?>