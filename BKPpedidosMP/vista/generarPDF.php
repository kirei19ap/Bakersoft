<?php
   session_start();
   if(!isset($_SESSION['user'])){
       header('Location: login.php');
   }
ob_start();
require '../../vendor/autoload.php';
require_once("../controlador/controladorPedido.php");
$obj = new controladorPedido();
use Dompdf\Dompdf;
use Dompdf\Options;

$idPedido = (int) $_GET['id'];
$pedido = $obj->traerDetallePedido($idPedido);
$proveedor = $obj->traerProveedorporNombre($pedido[0]['proveedor']);
// Crear contenido HTML
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Pedido Nº <?= $idPedido ?></title>
    <style>
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .logo {
        height: 40px;
        max-width: 50px;
    }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
        color: #333;
    }

    h2 {
        color: #2c3e50;
        border-bottom: 2px solid #2c3e50;
        padding-bottom: 5px;
    }

    .info-row {
        width: 100%;
        margin-bottom: 15px;
    }

    .info-cell {
        width: 48%;
        background-color: #ecf0f1;
        padding: 10px;
        border-radius: 5px;
        vertical-align: top;
    }

    .info-cell h5 {
        margin: 0 0 5px 0;
    }

    .info-cell p {
        margin: 4px 0;
    }

    .logo {
        width: 150px;
        height: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    th {
        background-color: #2980b9;
        color: white;
        padding: 8px;
        text-align: left;
    }

    td {
        padding: 8px;
        border: 1px solid #ccc;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    </style>
</head>

<body>

<table width="100%" cellspacing="0" cellpadding="0" style="border-bottom: 2px solid #2c3e50; margin-bottom: 20px;">
  <tr>
    <td style="font-family: DejaVu Sans, sans-serif; color: #2c3e50; font-size: 20px; font-weight: bold; padding-bottom: 5px;">
      Pedido Nº <?= $idPedido ?>
    </td>
    <td style="text-align: right; vertical-align: middle;">
    <img src="data:image/jpeg;base64,<?= base64_encode(file_get_contents('../../rsc/img/logo.jpg')) ?>" class="logo"
    alt="Logo Empresa" />
    </td>
  </tr>
</table>
    <table class="info-row">
        <tr>
            <td class="info-cell">
                <h5>Datos del Proveedor</h5>
                <p><strong>Proveedor:</strong> <?= $proveedor['nombre'] ?></p>
                <p><strong>Dirección:</strong> <?= $proveedor['calle']." ".$proveedor['altura']; ?></p>
                <p><strong>Teléfono:</strong> <?= $proveedor['telefono']; ?></p>
                <p><strong>Email:</strong> <?= $proveedor['email']; ?></p>
            </td>
            <td class="info-cell">
                <h5>Datos del Cliente</h5>
                <p><strong>Empresa:</strong> Bakersoft</p>
                <p><strong>Dirección:</strong> Joaquin V. Gonzales 2518</p>
                <p><strong>Teléfono:</strong> 0351-2529932</p>
                <p><strong>Email:</strong> contacto@generacion.com.ar</p>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Materia Prima</th>
                <th>Cantidad</th>
                <th>Unidad</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedido as $item): ?>
            <tr>
                <td><?= $item['materiaprima'] ?></td>
                <td><?= $item['cantidad'] ?></td>
                <td><?= $item['unidad_medida'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>
<?php
$html = ob_get_clean();

//Opciones de DomPDF
$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

//Salida PDF al navegador
$dompdf->stream("Pedido_$idPedido.pdf", ["Attachment" => false]);
exit;
?>