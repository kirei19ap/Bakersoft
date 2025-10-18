<?php
// controlador/buscar_empleados.php
if (function_exists('ob_get_level')) {
    while (ob_get_level() > 0) { ob_end_clean(); }
}
header('Content-Type: application/json; charset=utf-8');

try {
    // 1) Includes (ajustá paths si tu estructura difiere)
    require_once(__DIR__ . "/../../config/bd.php");               // Debe exponer $pdo (PDO)
    require_once(__DIR__ . "/../modelo/modeloadmempleado.php");

    // 2) Instanciar el modelo
    $modelo = new ModeloAdmEmpleado();

    // 3) Parametría DataTables
    $draw   = isset($_POST['draw'])   ? (int)$_POST['draw'] : 1;
    $start  = isset($_POST['start'])  ? max(0, (int)$_POST['start']) : 0;
    $length = isset($_POST['length']) ? min(200, max(1, (int)$_POST['length'])) : 10;

    // Búsqueda global
    $q = '';
    if (!empty($_POST['search']['value'])) {
        $q = trim((string)$_POST['search']['value']);
    }

    // Orden
    $orderColKey = 'apellido';
    $orderDir    = 'asc';
    if (!empty($_POST['order'][0]['column']) && isset($_POST['columns'])) {
        $idx = (int)$_POST['order'][0]['column'];
        $orderColKey = $_POST['columns'][$idx]['data'] ?? 'apellido';
        $dir = strtolower($_POST['order'][0]['dir'] ?? 'asc');
        $orderDir = ($dir === 'desc') ? 'desc' : 'asc';
    }

    // 4) Filtros del formulario
    $fEstado = $_POST['f_estado'] ?? null; // 'Activo' | 'Inactivo' | ''
    $fPuesto = isset($_POST['f_puesto']) && $_POST['f_puesto'] !== '' ? (int)$_POST['f_puesto'] : null;
    $fDesde  = $_POST['f_desde'] ?? null;  // 'YYYY-MM-DD' | ''
    $fHasta  = $_POST['f_hasta'] ?? null;

    foreach (['fEstado','fDesde','fHasta'] as $k) {
        if (isset($$k) && $$k === '') $$k = null;
    }

    // 5) Ejecutar búsqueda
    $res = $modelo->buscarEmpleadosDT(
        $q, $start, $length, $orderColKey, $orderDir, $fEstado, $fPuesto, $fDesde, $fHasta
    );

    // 6) Responder a DataTables
    echo json_encode([
        'draw'            => $draw,
        'recordsTotal'    => $res['total'],
        'recordsFiltered' => $res['filtrado'],
        'data'            => $res['rows']
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'draw'            => isset($draw) ? $draw : 0,
        'recordsTotal'    => 0,
        'recordsFiltered' => 0,
        'data'            => [],
        'error'           => 'Error en búsqueda: ' . $e->getMessage()
    ]);
    exit;
}
