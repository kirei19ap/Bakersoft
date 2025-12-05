<?php
// turnos/vista/guardar_asignaciones.php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once("../controlador/controladorTurnos.php");
$ctrl = new controladorTurnos();

// Leemos el cuerpo como JSON
$input = file_get_contents('php://input');
$data  = json_decode($input, true);

if (!is_array($data)) {
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'Datos de entrada inválidos.',
    ]);
    exit;
}

$fechaBase    = $data['fechaBase']    ?? '';
$idTurno      = $data['idTurno']      ?? '';
$asignaciones = $data['asignaciones'] ?? [];

// En el futuro podés ajustar el nombre de la variable de sesión
$usuarioId = $_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? null;

if (empty($fechaBase) || empty($idTurno)) {
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'Debés seleccionar una fecha y un turno.',
    ]);
    exit;
}

if (!is_array($asignaciones) || empty($asignaciones)) {
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'No se recibieron asignaciones para guardar.',
    ]);
    exit;
}

// En el payload, cada asignación viene como:
// {
//   idEmpleado: 1,
//   porDia: { '2025-12-01':'Asignado'|'SinTurno', ... }
// }

// Reorganizamos por fecha: fecha => [ ['idEmpleado'=>..,'estado'=>..], ... ]
$asignacionesPorFecha = [];

foreach ($asignaciones as $filaEmp) {
    $idEmpleado = $filaEmp['idEmpleado'] ?? null;
    $porDia     = $filaEmp['porDia']     ?? [];

    if (!$idEmpleado || !is_array($porDia)) {
        continue;
    }

    foreach ($porDia as $fecha => $estado) {
        if (!isset($asignacionesPorFecha[$fecha])) {
            $asignacionesPorFecha[$fecha] = [];
        }
        // Normalizamos estados: 'Asignado' o 'SinTurno'
        $estadoNormalizado = ($estado === 'Asignado') ? 'Asignado' : 'SinTurno';

        $asignacionesPorFecha[$fecha][] = [
            'idEmpleado' => (int)$idEmpleado,
            'estado'     => $estadoNormalizado
        ];
    }
}

// Vamos guardando día por día usando el controlador
$resultados  = [];
$conflictos  = [];
$algunoError = false;

foreach ($asignacionesPorFecha as $fecha => $listaDia) {
    $resp = $ctrl->guardarAsignaciones($fecha, $idTurno, $listaDia, $usuarioId ?? 0);
    $resultados[$fecha] = $resp;

    if (!$resp['ok']) {
        $algunoError = true;

        // Si hay conflictos de solapamiento, los guardamos
        if (!empty($resp['conflictos'])) {
            $conflictos[$fecha] = $resp['conflictos'];
        }

        // Guardamos también el mensaje del modelo para esa fecha
        if (!empty($resp['mensaje'])) {
            $errores[$fecha] = $resp['mensaje'];
        }
    }
}

if ($algunoError) {
    // Si hay conflictos de solapamiento, usamos ese mensaje
    if (!empty($conflictos)) {
        $mensaje = 'Se detectaron conflictos de solapamiento en alguna fecha. Revisá la planificación.';
    } elseif (!empty($errores)) {
        // Si no hay solapamientos pero sí otros errores, usamos el primero que aparezca
        $mensaje = reset($errores);
    } else {
        // Fallback genérico
        $mensaje = 'Ocurrió un error al guardar las asignaciones.';
    }

    echo json_encode([
        'ok'         => false,
        'mensaje'    => $mensaje,
        'conflictos' => $conflictos,
        'resultados' => $resultados,
        'errores'    => $errores
    ]);
} else {
    echo json_encode([
        'ok'        => true,
        'mensaje'   => 'Asignaciones de la semana guardadas correctamente.',
        'resultados'=> $resultados
    ]);
}
