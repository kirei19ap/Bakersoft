<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Menú Principal</title>
    <link rel="stylesheet" href="rsc/estilos/styleMenuPrincipal.css">
</head>
<body class="menu-fondo">
    <div class="menu-container">
        <h1>Menu Principal</h1>
        <div class="botones-menu">
            <button onclick="location.href='../../bakersoft/materiaprima/vista/index.php'">Gestión de Materia Prima</button>
            <button onclick="location.href='../rrhh.php'" disabled>Gestión de RRHH</button>
            <button onclick="location.href='../licencias_turnos.php'" disabled>Gestión de Licencias y Turnos</button>
            <button onclick="location.href='../usuarios.php'"disabled>Administración de Usuarios</button>
            <form action="../login.php" method="get">
    <button type="submit">Volver al Login</button>
</form> </div> </div>
<img src="rsc/img/LOGO.jfif" class="logo" alt="Logo de la aplicación">
</body>
</html>
