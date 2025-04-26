<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="rsc/estilos/styleLogin.css">
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
        <form method="POST" action="">
            <?php
            if (isset($errorLogin)){
                echo $errorLogin;
            }
            ?>
            <label>Usuario</label>
            <input type="text" name="usuario" required>
            
            <label>Contraseña</label>
            <input type="password" name="contrasena" required>
            
            <input type="submit" value="Iniciar Sesión">
        </form>
        
        <!-- Enlace para recuperación de contraseña (inactivo) -->
        <a href="recuperar.php">¿Olvidó su contraseña?</a>
    </div>

    <img src="rsc/img/LOGO.jfif" class="logo" alt="Logo de la aplicación">
</body>
</html>