<?php
header('Content-Type: application/json; charset=utf-8');
require_once(__DIR__."/../../config/bd.php");
$pdo = (new bd())->conexion();

$desde = (new DateTime('first day of -11 months'))->format('Y-m-01'); // últimos 12 meses

// Totales
$tot = $pdo->query("
  SELECT
    COUNT(*)                                                   AS total,
    SUM(estado='Activo'   AND eliminado=0)                     AS activos,
    SUM(estado='Inactivo' AND eliminado=0)                     AS inactivos,
    SUM(eliminado=1)                                          AS eliminados
  FROM usuarios
")->fetch(PDO::FETCH_ASSOC);

// Altas por mes (últimos 12)
$stAlt = $pdo->prepare("
  SELECT DATE_FORMAT(fecha_creacion,'%Y-%m') mes, COUNT(*) cant
  FROM usuarios
  WHERE fecha_creacion >= ?
  GROUP BY mes
  ORDER BY mes
");
$stAlt->execute([$desde]);
$altas = $stAlt->fetchAll(PDO::FETCH_ASSOC);

// Bajas por mes (lógico, últimos 12)
$stBaj = $pdo->prepare("
  SELECT DATE_FORMAT(fecha_baja,'%Y-%m') mes, COUNT(*) cant
  FROM usuarios
  WHERE eliminado=1 AND fecha_baja IS NOT NULL AND fecha_baja >= ?
  GROUP BY mes
  ORDER BY mes
");
$stBaj->execute([$desde]);
$bajas = $stBaj->fetchAll(PDO::FETCH_ASSOC);

// Distribución por rol (usuarios no eliminados)
$roles = $pdo->query("
  SELECT COALESCE(r.nombre_rol,'— Sin rol —') rol, COUNT(u.id) cant
  FROM roles r
  LEFT JOIN usuarios u ON u.rol = r.id_rol AND u.eliminado=0
  GROUP BY r.id_rol, r.nombre_rol
  UNION ALL
  SELECT '— Sin rol —' rol, COUNT(*) cant
  FROM usuarios u
  WHERE u.eliminado=0 AND (u.rol IS NULL OR u.rol=0)
  HAVING cant>0
")->fetchAll(PDO::FETCH_ASSOC);

// Eje de meses uniforme (para que altas/bajas tengan mismos meses)
$periodo = [];
$dt = new DateTime($desde);
for ($i=0; $i<12; $i++) {
  $periodo[] = $dt->format('Y-m');
  $dt->modify('+1 month');
}
// Map a arrays por mes
$mapA = array_fill_keys($periodo, 0);
$mapB = array_fill_keys($periodo, 0);
foreach ($altas as $a) { if (isset($mapA[$a['mes']])) $mapA[$a['mes']] = (int)$a['cant']; }
foreach ($bajas as $b) { if (isset($mapB[$b['mes']])) $mapB[$b['mes']] = (int)$b['cant']; }

// Respuesta
echo json_encode([
  'totales' => array_map('intval', $tot),
  'series'  => [
    'meses' => $periodo,
    'altas' => array_values($mapA),
    'bajas' => array_values($mapB),
  ],
  'roles'   => $roles,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
