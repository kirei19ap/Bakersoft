<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
require_once("../controlador/controladorReportes.php");
require_once '../../vendor/autoload.php';
require_once '../../config/bd.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$obj = new controladorReportes();

$datos = $obj->traerMPListado();

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header-table { width: 100%; margin-bottom: 20px; }
        .header-table td { vertical-align: middle; }
        .titulo { font-size: 20px; font-weight: bold; }
        .logo { width: 80px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>

<table class="header-table">
    <tr>
        <td class="titulo">Listado integral de Materias Primas</td>
        <td style="text-align: center;">
            <img src="data:image/jpeg;base64,<?= base64_encode(file_get_contents('../../rsc/img/logo.jpg')) ?>" class="logo" alt="Logo Empresa" />
        </td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Stock Actual</th>
            <th>Unidad de Medida</th>
            <th>Proveedor</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($datos as $fila): ?>
        <tr>
            <td><?= htmlspecialchars($fila['nombre']) ?></td>
            <td><?= $fila['stockactual'] ?></td>
            <td><?= htmlspecialchars($fila['unidad_medida']) ?></td>
            <td><?= $fila['proveedor'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>

<?php
$html = ob_get_clean();

// Generamos el PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Listado_MateriasPrimas.pdf", ["Attachment" => false]);
exit;
?>