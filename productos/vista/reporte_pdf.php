<?php
session_start();

// Modelo de productos
require_once("../modelo/modeloProductos.php");

// Dompdf (ajustá la ruta según tu instalación)
// Si usás Composer:
require_once '../../vendor/autoload.php';
// Si lo tenés manual en una carpeta dompdf:
// require_once '../../dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Leer datos del formulario
$modo  = $_POST['rep_modo'] ?? 'todos';      // 'todos' | 'rango'
$desde = $_POST['fecha_desde'] ?? null;
$hasta = $_POST['fecha_hasta'] ?? null;

// Validación básica y normalización
if ($modo === 'rango') {
    if (empty($desde) || empty($hasta)) {
        $_SESSION['flash_error'] = "Debe seleccionar ambas fechas para el reporte.";
        header("Location: reportes.php");
        exit;
    }
    if (strtotime($desde) === false || strtotime($hasta) === false || $desde > $hasta) {
        $_SESSION['flash_error'] = "Rango de fechas inválido para el reporte.";
        header("Location: reportes.php");
        exit;
    }
} else {
    // modo 'todos' → ignoramos fechas
    $desde = null;
    $hasta = null;
}

// Obtener datos desde el modelo
$modelo = new modeloProducto();
$rows   = $modelo->productosReporte($desde, $hasta);

// Título del reporte según modo
if ($modo === 'rango' && $desde && $hasta) {
    $titulo = "Reporte de Productos (Fecha alta: $desde al $hasta)";
} else {
    $titulo = "Reporte de Productos (Todos)";
}

// Armar HTML del reporte (simple, apto para dompdf)
$fechaGeneracion = date('d/m/Y H:i');

$html = '
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</title>
<style>
    body {
        font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
        font-size: 11px;
    }
    h1 {
        font-size: 18px;
        margin-bottom: 5px;
    }
    .subtitulo {
        font-size: 12px;
        margin-bottom: 15px;
        color: #555;
    }
    .footer {
        margin-top: 10px;
        font-size: 10px;
        color: #777;
        text-align: right;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    th, td {
        border: 1px solid #888;
        padding: 4px 6px;
        text-align: left;
        vertical-align: middle;
    }
    th {
        background-color: #f0f0f0;
        font-weight: bold;
    }
    td.text-center, th.text-center {
        text-align: center;
    }
</style>
</head>
<body>
    <h1>' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</h1>
    <div class="subtitulo">
        Generado el ' . $fechaGeneracion . '
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width:5%;">#</th>
                <th style="width:30%;">Nombre</th>
                <th style="width:22%;">Categoría</th>
                <th class="text-center" style="width:10%;">Unidad</th>
                <th class="text-center" style="width:10%;">Estado</th>
                <th class="text-center" style="width:23%;">Fecha alta</th>
            </tr>
        </thead>
        <tbody>';

if (count($rows) === 0) {
    $html .= '
            <tr>
                <td colspan="6" class="text-center">No se encontraron productos para los criterios seleccionados.</td>
            </tr>';
} else {
    $i = 1;
    foreach ($rows as $row) {
        $html .= '
            <tr>
                <td class="text-center">' . $i . '</td>
                <td>' . htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8') . '</td>
                <td>' . htmlspecialchars($row['categoria'], ENT_QUOTES, 'UTF-8') . '</td>
                <td class="text-center">' . htmlspecialchars($row['unidad_medida'], ENT_QUOTES, 'UTF-8') . '</td>
                <td class="text-center">' . htmlspecialchars($row['estado'], ENT_QUOTES, 'UTF-8') . '</td>
                <td class="text-center">' . htmlspecialchars($row['fecha_alta'], ENT_QUOTES, 'UTF-8') . '</td>
            </tr>';
        $i++;
    }
}

$html .= '
        </tbody>
    </table>

    <div class="footer">
        Bakersoft - Reporte de productos
    </div>
</body>
</html>';

// Configurar Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

// Horizontal (landscape)
$dompdf->setPaper('A4', 'landscape');

$dompdf->render();

// Mostrar en el navegador (inline). Cambiá Attachment a true si querés descarga directa.
$dompdf->stream('reporte_productos.pdf', ['Attachment' => false]);
exit;
