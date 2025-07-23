<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Error en la Operación</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f8d7da;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    .error-container {
      background-color: #ffffff;
      border: 1px solid #f5c2c7;
      border-left: 8px solid #dc3545;
      padding: 30px 40px;
      border-radius: 8px;
      max-width: 500px;
      text-align: center;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }

    .error-title {
      font-size: 24px;
      color: #dc3545;
      margin-bottom: 10px;
    }

    .error-message {
      font-size: 16px;
      color: #333333;
      margin-bottom: 20px;
    }

    .back-button {
      display: inline-block;
      padding: 10px 20px;
      background-color: #dc3545;
      color: white;
      text-decoration: none;
      border-radius: 4px;
      font-weight: bold;
    }

    .back-button:hover {
      background-color: #c82333;
    }
  </style>
</head>
<body>
  <div class="error-container">
    <h1 class="error-title">¡Ha ocurrido un error!</h1>
    <p class="error-message">No se pudo completar la operación actual. Por favor, intente nuevamente más tarde.</p>
    <a href="javascript:history.back()" class="back-button">Volver</a>
  </div>
</body>
</html>