<?php
// turnos/vista/cargar_operarios.php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once("../controlador/controladorTurnos.php");
$ctrl = new controladorTurnos();

$fechaDesde = $_POST['fechaDesde'] ?? '';
$fechaHasta = $_POST['fechaHasta'] ?? '';
$idTurno    = $_POST['idTurno']    ?? '';

if (empty($fechaDesde) || empty($fechaHasta) || empty($idTurno)) {
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'Debés seleccionar fecha desde, fecha hasta y un turno.',
        'data'    => []
    ]);
    exit;
}

try {
    $dtDesde = new DateTime($fechaDesde);
    $dtHasta = new DateTime($fechaHasta);
} catch (Exception $e) {
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'Alguna de las fechas indicadas no es válida.',
        'data'    => []
    ]);
    exit;
}

if ($dtHasta < $dtDesde) {
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'La fecha hasta no puede ser anterior a la fecha desde.',
        'data'    => []
    ]);
    exit;
}

// Limitamos a 7 días para que la grilla sea manejable
$diff = $dtDesde->diff($dtHasta)->days;
if ($diff > 6) { // 0 = mismo día, 6 = 7 días
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'El rango no puede superar los 7 días.',
        'data'    => []
    ]);
    exit;
}

// Armamos el arreglo de días del rango
$diasRango = [];
$dtActual  = clone $dtDesde;

while ($dtActual <= $dtHasta) {
    // Etiqueta de día: Lun, Mar, Mié, etc.
    $diaSemana = (int)$dtActual->format('N'); // 1 (Lun) a 7 (Dom)
    $labels = [1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb', 7 => 'Dom'];

    $diasRango[] = [
        'fecha'      => $dtActual->format('Y-m-d'),
        'etiqueta'   => $labels[$diaSemana],
        'labelCorta' => $labels[$diaSemana]
    ];

    $dtActual->modify('+1 day');
}

// Ahora pedimos al controlador los operarios para cada día
$empleadosPorDia = [];
foreach ($diasRango as $infoDia) {
    $resp = $ctrl->obtenerOperariosConAsignacion($infoDia['fecha'], $idTurno);
    if (!$resp['ok']) {
        echo json_encode([
            'ok'      => false,
            'mensaje' => $resp['mensaje'] ?: 'Error al obtener operarios.',
            'data'    => []
        ]);
        exit;
    }
    $empleadosPorDia[$infoDia['fecha']] = $resp['data'];
}

// Unificamos por empleado a nivel rango
$empleadosRango = [];
foreach ($diasRango as $infoDia) {
    $fechaDia = $infoDia['fecha'];
    foreach ($empleadosPorDia[$fechaDia] as $row) {
        $idEmp = $row['id_empleado'];

        if (!isset($empleadosRango[$idEmp])) {
            $empleadosRango[$idEmp] = [
                'id_empleado'     => $row['id_empleado'],
                'legajo'          => $row['legajo'],
                'nombre'          => $row['nombre'],
                'apellido'        => $row['apellido'],
                'nombreCompleto'  => trim($row['apellido'] . ', ' . $row['nombre']),
                'descrPuesto'     => $row['descrPuesto'],
                'estadoEmpleado'  => $row['estadoEmpleado'],
                'asignaciones'    => [] // fecha => estado (null | Asignado | Confirmado | Finalizado)
            ];
        }

        $empleadosRango[$idEmp]['asignaciones'][$fechaDia] = $row['estadoAsignacion'] ?? null;
    }
}

// Pasamos a array indexado
$empleadosRango = array_values($empleadosRango);

// Devolvemos usando la misma clave "semana" que antes, pero ahora es un "rango"
echo json_encode([
    'ok'      => true,
    'mensaje' => '',
    'semana'  => [ // dejamos el nombre "semana" para no tocar guardar_asignaciones ni el front antiguo
        'inicio' => $dtDesde->format('Y-m-d'),
        'fin'    => $dtHasta->format('Y-m-d'),
        'dias'   => $diasRango
    ],
    'data'    => $empleadosRango
]);
