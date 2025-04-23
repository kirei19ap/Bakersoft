<?php
// Iniciar sesión para el ingreso del usuario
session_start();

// vinculamos con la conexion a la BD
include('conexion.php'); // "Conexión a la base de datos"


 /* Estrc condiconal del formulario de login cuando se envían los datos ingresados por el usuario */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener datos del formulario
    $usuario = $_POST['usuario'];      // Nombre de usuario/correo ingresado
    $contrasena = $_POST['contrasena']; // Contraseña ingresada


    //Consulta SQL 

    $sql = "SELECT * FROM usuarios WHERE usuario = ? AND contrasena = MD5(?)"; //MD5 se usa para hashear la contraseña (más seguro)
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $usuario, $contrasena);
    $stmt->execute();
    $resultado = $stmt->get_result();

    // verificacion para ver si se encontró el usuario
    if ($resultado->num_rows > 0) {
        // Usuario válido 
        $_SESSION['usuario'] = $usuario;
        
        // Redirige a nuestro menu pricnipal 
        header('Location: MenuPrincipal/MenuPrincipal.php');
        exit();
    } else {
        // Credenciales inválidas 
        $_SESSION['error'] = "Usuario o contraseña incorrectos. Por favor, intente nuevamente.";
    }
    
    // Cerrar el statement
    $stmt->close();
}

// Cerrar la conexión a la base de datos
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="styleLogin.css">
</head>
<body>
    <!-- Mostramos alerta si existe mensaje de error -->
    <?php
    if (isset($_SESSION['error'])) {
        echo "<script>alert('" . $_SESSION['error'] . "');</script>";
        unset($_SESSION['error']); // Limpiar el mensaje después de mostrarlo
    }
    ?>

    <div class="login-box"> 
        <form method="POST" action="login.php">
            <label>Usuario</label>
            <input type="text" name="usuario" required>
            
            <label>Contraseña</label>
            <input type="password" name="contrasena" required>
            
            <input type="submit" value="Iniciar Sesión">
        </form>
        
        <!-- Enlace para recuperación de contraseña (inactivo) -->
        <a href="recuperar.php">¿Olvidó su contraseña?</a>
    </div>

    <img src="Imagenes/LOGO.jfif" class="logo" alt="Logo de la aplicación">
</body>
</html>