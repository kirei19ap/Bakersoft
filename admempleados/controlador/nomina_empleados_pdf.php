<?php
// admempleados/controlador/nomina_empleados_pdf.php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('html_errors', '0');
error_reporting(E_ALL);
if (function_exists('ob_get_level')) {
  while (ob_get_level() > 0) { ob_end_clean(); }
}

try {
  // === Rutas ===
  $ROOT   = dirname(__DIR__, 2);              // .../Bakersoft
  $MODULE = dirname(__DIR__);                 // .../Bakersoft/admempleados

  require_once $ROOT   . '/config/bd.php';
  require_once $MODULE . '/modelo/modeloadmempleado.php';

  // Dompdf
  require_once $ROOT . '/vendor/autoload.php'; // ajustá si tu autoload está en otra ruta
  $options = new \Dompdf\Options();
  $options->set('isHtml5ParserEnabled', true);
  $options->set('isRemoteEnabled', true);

  // Conexión
  $bd  = new bd();
  $pdo = $bd->conexion();
  if (!$pdo instanceof PDO) { throw new RuntimeException('Sin conexión PDO'); }
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->exec("SET NAMES utf8mb4");

  $modelo = new ModeloAdmEmpleado(); // tu constructor ya crea PDO internamente

  // ===== Filtros GET =====
  $q        = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
  $fEstado  = isset($_GET['f_estado']) ? trim((string)$_GET['f_estado']) : '';
  $fPuesto  = (isset($_GET['f_puesto']) && $_GET['f_puesto'] !== '') ? (int)$_GET['f_puesto'] : null;
  $fDesde   = isset($_GET['f_desde']) ? trim((string)$_GET['f_desde']) : '';
  $fHasta   = isset($_GET['f_hasta']) ? trim((string)$_GET['f_hasta']) : '';
  $orderCol = isset($_GET['order_col']) ? trim((string)$_GET['order_col']) : 'apellido';
  $orderDir = isset($_GET['order_dir']) ? trim((string)$_GET['order_dir']) : 'asc';

  // Datos
  $rows = $modelo->listarNomina(
    $q ?: null,
    $fEstado ?: null,
    $fPuesto,
    $fDesde ?: null,
    $fHasta ?: null,
    $orderCol,
    $orderDir
  );

  // ===== HTML =====
  $titulo = 'Nómina de empleados';
  $fecha  = date('d/m/Y H:i');
  

  ob_start();
  ?>
  <!doctype html>
  <html lang="es">
  <head>
    <meta charset="utf-8">
    <style>
      body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; }
      h1 { font-size: 18px; margin: 0 0 6px 0; }
      .sub { color: #666; font-size: 11px; margin-bottom: 12px; }
      table { width: 100%; border-collapse: collapse; }
      th, td { border: 0px solid #ccc; padding: 6px 8px; }
      th { background: #f2f2f2; text-align: left; }
      .center { text-align: center; }
      .right { text-align: right; }
      .nowrap { white-space: nowrap; }
      .logo {
        height: 50px;
        max-width: 50px;
    }
    </style>
  </head>
  <body>
    <table width="100%" cellspacing="0" cellpadding="0" style="border-bottom: 2px solid #2c3e50; margin-bottom: 20px;">
      <tr>
      <td><h1><?= htmlspecialchars($titulo) ?></h1>
    <div class="sub">Generado: <?= htmlspecialchars($fecha) ?></div><td>
      <td style="text-align: right; vertical-align: middle;">
    <img src="data:image/jpeg;base64,<?= base64_encode(file_get_contents('../../rsc/img/logo.jpg')) ?>" class="logo"
    alt="Logo Empresa" />
    </td>
      </tr>
    </table>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Apellido</th>
          <th>Nombre</th>
          <th>DNI</th>
          <th>Legajo</th>
          <th>Puesto</th>
          <th>Estado</th>
          <th>Fecha ingreso</th>
          <th>Provincia</th>
          <th>Localidad</th>
          <th>Email</th>
          <th>Teléfono</th>
          <th>Estado civil</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="13" class="center">Sin resultados</td></tr>
        <?php else: ?>
          <?php $i=1; foreach ($rows as $r): ?>
            <tr>
              <td class="center"><?= $i++ ?></td>
              <td><?= htmlspecialchars($r['apellido'] ?? '') ?></td>
              <td><?= htmlspecialchars($r['nombre'] ?? '') ?></td>
              <td class="nowrap"><?= htmlspecialchars($r['dni'] ?? '') ?></td>
              <td><?= htmlspecialchars($r['legajo'] ?? '') ?></td>
              <td><?= htmlspecialchars($r['puesto'] ?? '') ?></td>
              <td><?= htmlspecialchars($r['estado'] ?? '') ?></td>
              <td class="nowrap"><?= htmlspecialchars($r['fecha_ingreso'] ?? '') ?></td>
              <td><?= htmlspecialchars($r['provincia'] ?? '') ?></td>
              <td><?= htmlspecialchars($r['localidad'] ?? '') ?></td>
              <td><?= htmlspecialchars($r['email'] ?? '') ?></td>
              <td><?= htmlspecialchars($r['telefono'] ?? '') ?></td>
              <td><?= htmlspecialchars($r['estado_civil'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </body>
  </html>
  <?php
  $html = ob_get_clean();

  // Render
  $dompdf = new \Dompdf\Dompdf($options);
  $dompdf->loadHtml($html, 'UTF-8');
  $dompdf->setPaper('A4', 'landscape');
  $dompdf->render();
  $dompdf->stream('nomina_empleados_'.date('Ymd_His').'.pdf', ['Attachment' => 0]);
  exit;

} catch (Throwable $e) {
  http_response_code(500);
  header('Content-Type: text/plain; charset=utf-8');
  echo 'Error al generar PDF: ' . $e->getMessage();
  exit;
}
