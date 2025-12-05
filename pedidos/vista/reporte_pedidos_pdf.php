<?php
require_once("../controlador/controladorPedidos.php");

// Ajusta esta ruta al autoload de Dompdf en tu proyecto
require_once("../../vendor/autoload.php"); // si usás composer con dompdf/dompdf

use Dompdf\Dompdf;

$ctrl = new controladorPedidos();

// Filtros desde POST
$hoy    = date('Y-m-d');
$hace30 = date('Y-m-d', strtotime('-30 days'));

$fechaDesde = $_POST['desde'] ?? $hace30;
$fechaHasta = $_POST['hasta'] ?? $hoy;
$estadoSel  = isset($_POST['estado']) && $_POST['estado'] !== '' ? (int)$_POST['estado'] : null;

$pedidos = $ctrl->obtenerPedidosFiltrados($fechaDesde, $fechaHasta, $estadoSel);

// Helper de estado (lo mismo que en reportes_listado)
function obtenerDescEstado(int $estado, ?string $descBD): string {
    if ($descBD) return $descBD;

    switch ($estado) {
        case 70: return 'Generado';
        case 80: return 'Confirmado';
        case 90: return 'Preparado';
        case 100: return 'Entregado';
        case 60: return 'Cancelado';
        default: return 'Desconocido';
    }
}

$estadosOpciones = [
    ''   => 'Todos',
    70   => 'Generado',
    80   => 'Confirmado',
    90   => 'Preparado',
    100  => 'Entregado',
    60   => 'Cancelado',
];

$textoEstado = 'Todos';
if (!is_null($estadoSel) && isset($estadosOpciones[$estadoSel])) {
    $textoEstado = $estadosOpciones[$estadoSel];
}

$cantidadPedidos = count($pedidos);
$totalFacturacion = array_reduce($pedidos, function($acum, $p) {
    return $acum + (float)$p['total'];
}, 0.0);

// Armamos el HTML del reporte
ob_start();
?>

<html>
<head>
  <meta charset="utf-8">
  <style>
    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 11px;
    }
    h1, h2, h3 {
      margin: 0;
      padding: 0;
    }
    .encabezado {
      text-align: center;
      margin-bottom: 10px;
    }
    .resumen {
      margin-bottom: 10px;
      font-size: 11px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 5px;
    }
    th, td {
      border: 1px solid #333;
      padding: 4px;
    }
    th {
      background-color: #f0f0f0;
    }
    .text-right {
      text-align: right;
    }
    .text-center {
      text-align: center;
    }
  </style>
</head>
<body>

  <div class="encabezado">
    <h2>Reporte de pedidos</h2>
    <small>Generado el <?php echo date('d/m/Y H:i'); ?></small>
  </div>

  <div class="resumen">
    <strong>Período:</strong>
    <?php echo date('d/m/Y', strtotime($fechaDesde)); ?>
    al
    <?php echo date('d/m/Y', strtotime($fechaHasta)); ?>
    <br>
    <strong>Estado:</strong> <?php echo htmlspecialchars($textoEstado); ?><br>
    <strong>Cantidad de pedidos:</strong> <?php echo $cantidadPedidos; ?><br>
    <strong>Facturación total:</strong> $ <?php echo number_format($totalFacturacion, 2, ',', '.'); ?>
  </div>

  <table>
    <thead>
      <tr>
        <th style="width: 10%;"># Pedido</th>
        <th style="width: 15%;">Fecha</th>
        <th style="width: 35%;">Cliente</th>
        <th style="width: 20%;">Estado</th>
        <th style="width: 20%;">Total</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($pedidos)): ?>
        <?php foreach ($pedidos as $p): ?>
          <?php
            $fechaRaw = $p['fechaPedido'];
            $fechaTexto = '';
            $dt = DateTime::createFromFormat('Y-m-d H:i:s', $fechaRaw);
            if ($dt) {
                $fechaTexto = $dt->format('d/m/Y');
            } else {
                $fechaTexto = date('d/m/Y', strtotime($fechaRaw));
            }

            $descEstado = obtenerDescEstado((int)$p['estado'], $p['descEstado'] ?? null);
          ?>
          <tr>
            <td class="text-center"><?php echo (int)$p['idPedidoVenta']; ?></td>
            <td class="text-center"><?php echo $fechaTexto; ?></td>
            <td><?php echo htmlspecialchars($p['cliente']); ?></td>
            <td><?php echo htmlspecialchars($descEstado); ?></td>
            <td class="text-right">
              $ <?php echo number_format($p['total'], 2, ',', '.'); ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" class="text-center">
            No se encontraron pedidos para el filtro seleccionado.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

</body>
</html>

<?php
$html = ob_get_clean();

// Generar PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape'); // horizontal
$dompdf->render();
$dompdf->stream('reporte_pedidos.pdf', ['Attachment' => 1]);
