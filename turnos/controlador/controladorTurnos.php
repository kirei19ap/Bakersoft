<?php

class controladorTurnos
{

    private $modelo;

    public function __construct()
    {
        // Ajustá la ruta si tu estructura de carpetas difiere
        require_once __DIR__ . ("/../modelo/modeloTurnos.php");
        $this->modelo = new modeloTurnos();
    }

    /**
     * Devuelve los turnos laborales activos.
     * Usado para llenar el <select> de turnos en la vista.
     */
    public function obtenerTurnosActivos()
    {
        return $this->modelo->obtenerTurnosActivos();
    }

    /**
     * Devuelve la lista de operarios con su estado de asignación
     * para una fecha y turno determinados.
     *
     * @param string $fecha   Formato 'Y-m-d'
     * @param int    $idTurno
     * @return array
     */
    public function obtenerOperariosConAsignacion($fecha, $idTurno)
    {
        // Validación básica de parámetros (sin inventar reglas de negocio nuevas)
        if (!$this->esFechaValida($fecha)) {
            return [
                'ok'      => false,
                'mensaje' => 'La fecha indicada no es válida.',
                'data'    => []
            ];
        }

        if (empty($idTurno) || !is_numeric($idTurno)) {
            return [
                'ok'      => false,
                'mensaje' => 'El turno indicado no es válido.',
                'data'    => []
            ];
        }

        $turno = (int)$idTurno;
        $lista = $this->modelo->obtenerOperariosConAsignacion($fecha, $turno);

        return [
            'ok'      => true,
            'mensaje' => '',
            'data'    => $lista
        ];
    }

    /**
     * Guarda las asignaciones para una fecha y turno.
     *
     * $asignaciones debe ser un array con elementos del tipo:
     *   ['idEmpleado' => 1, 'estado' => 'Asignado' | 'SinTurno']
     *
     * El modelo se encarga de la lógica de solapamientos y guardado.
     */
    public function guardarAsignaciones($fecha, $idTurno, $asignaciones, $usuarioId)
    {
        if (!$this->esFechaValida($fecha)) {
            return [
                'ok'         => false,
                'mensaje'    => 'La fecha indicada no es válida.',
                'conflictos' => []
            ];
        }

        if (empty($idTurno) || !is_numeric($idTurno)) {
            return [
                'ok'         => false,
                'mensaje'    => 'El turno indicado no es válido.',
                'conflictos' => []
            ];
        }

        if (!is_array($asignaciones)) {
            return [
                'ok'         => false,
                'mensaje'    => 'El formato de asignaciones no es válido.',
                'conflictos' => []
            ];
        }

        $turno = (int)$idTurno;
        $usuario = (int)$usuarioId;

        return $this->modelo->guardarAsignaciones($fecha, $turno, $asignaciones, $usuario);
    }

    /**
     * Valida que la fecha tenga un formato Y-m-d correcto.
     */
    private function esFechaValida($fecha)
    {
        if (empty($fecha)) {
            return false;
        }
        $d = DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }

    /**
     * Listado de asignaciones para el index de turnos.
     *
     * Filtros opcionales:
     *  - fechaDesde / fechaHasta (Y-m-d)
     *  - idTurno
     *  - estado ('Asignado','Confirmado','Finalizado')
     */
    public function listarAsignaciones($fechaDesde = null, $fechaHasta = null, $idTurno = null, $estado = null)
    {
        // Validamos fechas si vienen
        if (!empty($fechaDesde) && !$this->esFechaValida($fechaDesde)) {
            $fechaDesde = null;
        }
        if (!empty($fechaHasta) && !$this->esFechaValida($fechaHasta)) {
            $fechaHasta = null;
        }

        if (!empty($idTurno) && !is_numeric($idTurno)) {
            $idTurno = null;
        }

        if (!in_array($estado, ['Asignado', 'Confirmado', 'Finalizado', 'Cancelado', ''], true)) {
            $estado = null;
        }

        return $this->modelo->obtenerAsignacionesDetalle($fechaDesde, $fechaHasta, $idTurno, $estado);
    }

    public function verAsignacion($idAsignacion)
    {
        if (empty($idAsignacion) || !is_numeric($idAsignacion)) {
            return [
                'ok'      => false,
                'mensaje' => 'ID de asignación inválido.',
                'data'    => null
            ];
        }

        $data = $this->modelo->obtenerAsignacionPorId((int)$idAsignacion);
        if (!$data) {
            return [
                'ok'      => false,
                'mensaje' => 'La asignación indicada no existe.',
                'data'    => null
            ];
        }

        return [
            'ok'      => true,
            'mensaje' => '',
            'data'    => $data
        ];
    }

    public function eliminarAsignacion($idAsignacion)
    {
        if (empty($idAsignacion) || !is_numeric($idAsignacion)) {
            return [
                'ok'      => false,
                'mensaje' => 'ID de asignación inválido.'
            ];
        }

        return $this->modelo->eliminarAsignacion((int)$idAsignacion);
    }

    public function obtenerDatosReasignacion($idAsignacion)
    {
        if (empty($idAsignacion) || !is_numeric($idAsignacion)) {
            return [
                'ok'      => false,
                'mensaje' => 'ID de asignación inválido.'
            ];
        }

        return $this->modelo->obtenerDatosReasignacion((int)$idAsignacion);
    }

    public function reasignarAsignacion($idAsignacion, $idEmpleadoNuevo, $usuarioId)
    {
        if (empty($idAsignacion) || !is_numeric($idAsignacion)) {
            return [
                'ok'      => false,
                'mensaje' => 'ID de asignación inválido.'
            ];
        }
        if (empty($idEmpleadoNuevo) || !is_numeric($idEmpleadoNuevo)) {
            return [
                'ok'      => false,
                'mensaje' => 'Operario seleccionado inválido.'
            ];
        }

        return $this->modelo->reasignarAsignacion((int)$idAsignacion, (int)$idEmpleadoNuevo, (int)$usuarioId);
    }

    public function listarTurnosEmpleado($idEmpleado, $fechaDesde, $fechaHasta, $estado = null)
    {
        if (empty($idEmpleado) || !is_numeric($idEmpleado)) {
            return [
                'ok'      => false,
                'mensaje' => 'Empleado inválido.',
                'data'    => []
            ];
        }

        // Validamos fechas, si vienen vacías establecemos un rango por defecto
        if (empty($fechaDesde) || !$this->esFechaValida($fechaDesde)) {
            $fechaDesde = date('Y-m-d');
        }
        if (empty($fechaHasta) || !$this->esFechaValida($fechaHasta)) {
            $fechaHasta = date('Y-m-d', strtotime('+14 days'));
        }

        if ($fechaHasta < $fechaDesde) {
            $tmp        = $fechaDesde;
            $fechaDesde = $fechaHasta;
            $fechaHasta = $tmp;
        }

        $estadoFiltrado = null;
        if (in_array($estado, ['Asignado', 'Confirmado', 'Finalizado'], true)) {
            $estadoFiltrado = $estado;
        }

        $datos = $this->modelo->obtenerTurnosDeEmpleado(
            (int)$idEmpleado,
            $fechaDesde,
            $fechaHasta,
            $estadoFiltrado
        );

        return [
            'ok'      => true,
            'mensaje' => '',
            'data'    => $datos
        ];
    }

    public function actualizarEstadoTurnoEmpleado($idAsignacion, $idEmpleado, $nuevoEstado, $usuarioId)
    {
        if (empty($idAsignacion) || !is_numeric($idAsignacion)) {
            return [
                'ok'      => false,
                'mensaje' => 'ID de asignación inválido.'
            ];
        }
        if (empty($idEmpleado) || !is_numeric($idEmpleado)) {
            return [
                'ok'      => false,
                'mensaje' => 'Empleado inválido.'
            ];
        }

        if (!in_array($nuevoEstado, ['Confirmado', 'Finalizado'], true)) {
            return [
                'ok'      => false,
                'mensaje' => 'Estado solicitado inválido.'
            ];
        }

        return $this->modelo->cambiarEstadoTurnoEmpleado(
            (int)$idAsignacion,
            (int)$idEmpleado,
            $nuevoEstado,
            (int)$usuarioId
        );
    }

    public function obtenerEmpleadoPorUsuarioId($usuarioId)
    {
        if (empty($usuarioId)) {
            return null;
        }
        return $this->modelo->obtenerEmpleadoPorUsuarioId($usuarioId);
    }

    public function contarPendientesPorEmpleado($idEmpleado, $fechaDesde, $fechaHasta)
    {
        if (empty($idEmpleado) || !is_numeric($idEmpleado)) {
            return [
                'ok'        => false,
                'mensaje'   => 'Empleado inválido.',
                'pendientes' => 0
            ];
        }

        // Validamos fechas mínimamente
        if (empty($fechaDesde) || !$this->esFechaValida($fechaDesde)) {
            $fechaDesde = date('Y-m-d');
        }
        if (empty($fechaHasta) || !$this->esFechaValida($fechaHasta)) {
            $fechaHasta = date('Y-m-d', strtotime('+14 days'));
        }
        if ($fechaHasta < $fechaDesde) {
            $tmp        = $fechaDesde;
            $fechaDesde = $fechaHasta;
            $fechaHasta = $tmp;
        }

        $cantidad = $this->modelo->contarTurnosPendientesEmpleado(
            (int)$idEmpleado,
            $fechaDesde,
            $fechaHasta
        );

        return [
            'ok'        => true,
            'mensaje'   => '',
            'pendientes' => $cantidad
        ];
    }

    public function contarPendientesPorUsuarioLogin($usuarioLogin, $fechaDesde, $fechaHasta)
    {
        if (empty($usuarioLogin)) {
            return [
                'ok'        => false,
                'mensaje'   => 'Usuario inválido.',
                'pendientes' => 0
            ];
        }

        // Reusamos el método que ya tenemos
        $empleado = $this->modelo->obtenerEmpleadoPorUsuarioId($usuarioLogin);
        if (!$empleado || empty($empleado['id_empleado'])) {
            return [
                'ok'        => false,
                'mensaje'   => 'No se encontró empleado asociado al usuario.',
                'pendientes' => 0
            ];
        }

        return $this->contarPendientesPorEmpleado(
            (int)$empleado['id_empleado'],
            $fechaDesde,
            $fechaHasta
        );
    }

    public function obtenerDatosCalendarioProduccion($fechaDesde, $fechaHasta)
    {
        // Validación básica de fechas
        if (empty($fechaDesde) || !$this->esFechaValida($fechaDesde)) {
            $fechaDesde = date('Y-m-01'); // primer día del mes actual
        }
        if (empty($fechaHasta) || !$this->esFechaValida($fechaHasta)) {
            $fechaHasta = date('Y-m-t'); // último día del mes actual
        }
        if ($fechaHasta < $fechaDesde) {
            $tmp        = $fechaDesde;
            $fechaDesde = $fechaHasta;
            $fechaHasta = $tmp;
        }

        // Turnos activos
        $turnosActivos = $this->modelo->obtenerTurnosActivos();

        // Resumen de asignaciones por fecha y turno
        $resumen = $this->modelo->obtenerResumenAsignacionesPorFecha($fechaDesde, $fechaHasta);

        return [
            'ok'      => true,
            'mensaje' => '',
            'turnos'  => $turnosActivos,
            'data'    => $resumen
        ];
    }

    public function solicitarCambioTurnoEmpleado($idAsignacion, $idEmpleado, $tipo, $motivo)
    {
        if (empty($idAsignacion) || !is_numeric($idAsignacion)) {
            return [
                'ok'      => false,
                'mensaje' => 'Asignación inválida.'
            ];
        }
        if (empty($idEmpleado) || !is_numeric($idEmpleado)) {
            return [
                'ok'      => false,
                'mensaje' => 'Empleado inválido.'
            ];
        }

        $tipo = ($tipo === 'Cambio') ? 'Cambio' : 'Rechazo';
        $motivo = trim($motivo ?? '');
        if ($motivo === '') {
            return [
                'ok'      => false,
                'mensaje' => 'Debe indicar un motivo para la solicitud.'
            ];
        }

        // Verificamos que la asignación exista y pertenezca al empleado logueado
        $asignacion = $this->modelo->obtenerAsignacionDeEmpleado((int)$idAsignacion, (int)$idEmpleado);

        if (!$asignacion) {
            return [
                'ok'      => false,
                'mensaje' => 'No se encontró una asignación de turno para el empleado logueado.'
            ];
        }


        // Sólo permitimos solicitar cambio para estados Asignado o Confirmado
        if (!in_array($asignacion['estado'], ['Asignado', 'Confirmado'], true)) {
            return [
                'ok'      => false,
                'mensaje' => 'Sólo se puede solicitar cambio para turnos Asignados o Confirmados.'
            ];
        }

        // Verificamos que no haya ya una solicitud pendiente
        if ($this->modelo->tieneSolicitudPendiente((int)$idAsignacion, (int)$idEmpleado)) {
            return [
                'ok'      => false,
                'mensaje' => 'Ya existe una solicitud pendiente para este turno.'
            ];
        }

        $ok = $this->modelo->crearSolicitudTurno((int)$idAsignacion, (int)$idEmpleado, $tipo, $motivo);
        if (!$ok) {
            return [
                'ok'      => false,
                'mensaje' => 'No se pudo registrar la solicitud. Intente nuevamente.'
            ];
        }

        return [
            'ok'      => true,
            'mensaje' => 'La solicitud fue registrada y será revisada por el área de producción.'
        ];
    }

    public function obtenerSolicitudesPendientes()
    {
        $solicitudes = $this->modelo->obtenerSolicitudesTurno('Pendiente');

        return [
            'ok'    => true,
            'data'  => $solicitudes,
            'mensaje' => ''
        ];
    }

    /**
     * Gestiona una solicitud de turno (aprobar o rechazar).
     * Si se aprueba, opcionalmente marcamos la asignación como Cancelado.
     */
    public function gestionarSolicitudTurno($idSolicitud, $accion)
    {
        if (empty($idSolicitud) || !is_numeric($idSolicitud)) {
            return [
                'ok'      => false,
                'mensaje' => 'Solicitud inválida.'
            ];
        }

        $accion = strtolower(trim($accion));
        if (!in_array($accion, ['aprobar', 'rechazar'], true)) {
            return [
                'ok'      => false,
                'mensaje' => 'Acción inválida.'
            ];
        }

        $solicitud = $this->modelo->obtenerSolicitudPorId((int)$idSolicitud);
        if (!$solicitud) {
            return [
                'ok'      => false,
                'mensaje' => 'No se encontró la solicitud.'
            ];
        }

        if ($solicitud['estado'] !== 'Pendiente') {
            return [
                'ok'      => false,
                'mensaje' => 'La solicitud ya fue gestionada.'
            ];
        }

        $nuevoEstadoSolicitud = ($accion === 'aprobar') ? 'Aprobada' : 'Rechazada';

        // Si la aprobamos, podemos marcar la asignación como Cancelado
        if ($accion === 'aprobar') {
            $this->modelo->actualizarEstadoAsignacion(
                (int)$solicitud['idAsignacion'],
                'Cancelado'    // o 'Ausente', según lo que estés usando
            );
        }

        // Por ahora no estamos guardando el usuarioRespuesta (lo dejamos en NULL)
        $ok = $this->modelo->actualizarEstadoSolicitud(
            (int)$idSolicitud,
            $nuevoEstadoSolicitud,
            null
        );

        if (!$ok) {
            return [
                'ok'      => false,
                'mensaje' => 'No se pudo actualizar la solicitud.'
            ];
        }

        $mensaje = ($accion === 'aprobar')
            ? 'La solicitud fue aprobada. El turno quedó marcado como cancelado.'
            : 'La solicitud fue rechazada.';

        return [
            'ok'      => true,
            'mensaje' => $mensaje
        ];
    }

    public function obtenerCantidadSolicitudesPendientes()
    {
        $cantidad = $this->modelo->contarSolicitudesPendientes();

        return [
            'ok'         => true,
            'pendientes' => $cantidad,
            'mensaje'    => ''
        ];
    }

    public function obtenerOperariosActivos()
    {
        return $this->modelo->obtenerOperariosActivos();
    }

    public function obtenerReporteTurnos($idEmpleado, $fechaDesde, $fechaHasta, $estado = '')
    {
        // Validaciones básicas de fecha
        if (!$this->esFechaValida($fechaDesde)) {
            $fechaDesde = date('Y-m-01');
        }
        if (!$this->esFechaValida($fechaHasta)) {
            $fechaHasta = date('Y-m-t');
        }
        if ($fechaHasta < $fechaDesde) {
            $tmp        = $fechaDesde;
            $fechaDesde = $fechaHasta;
            $fechaHasta = $tmp;
        }

        return $this->modelo->obtenerListadoTurnosDetalle(
            $idEmpleado ? (int)$idEmpleado : null,
            $fechaDesde,
            $fechaHasta,
            $estado
        );
    }

    public function obtenerDashboardTurnos($fechaDesde, $fechaHasta)
    {
        // Validar fechas
        if (!$this->esFechaValida($fechaDesde)) {
            $fechaDesde = date('Y-m-01');
        }
        if (!$this->esFechaValida($fechaHasta)) {
            $fechaHasta = date('Y-m-t');
        }
        if ($fechaHasta < $fechaDesde) {
            $tmp        = $fechaDesde;
            $fechaDesde = $fechaHasta;
            $fechaHasta = $tmp;
        }

        // Distribución de estados
        $resumenEstados = $this->modelo->obtenerResumenEstadosTurnos($fechaDesde, $fechaHasta);

        $totalesPorEstado = [
            'Asignado'   => 0,
            'Confirmado' => 0,
            'Finalizado' => 0,
            'Cancelado'  => 0,
            'Otros'      => 0,
        ];
        $totalTurnos = 0;

        foreach ($resumenEstados as $row) {
            $estado = $row['estado'] ?? '';
            $cant   = (int)($row['cantidad'] ?? 0);

            if (isset($totalesPorEstado[$estado])) {
                $totalesPorEstado[$estado] += $cant;
            } else {
                $totalesPorEstado['Otros'] += $cant;
            }
            $totalTurnos += $cant;
        }

        // Cálculo de KPIs
        $finalizados = $totalesPorEstado['Finalizado'] ?? 0;
        $asignados   = $totalesPorEstado['Asignado']   ?? 0;
        $confirmados = $totalesPorEstado['Confirmado'] ?? 0;
        $cancelados  = $totalesPorEstado['Cancelado']  ?? 0;

        $denCumplimiento = $asignados + $confirmados + $finalizados;
        $porcCumplimiento = $denCumplimiento > 0
            ? round(($finalizados / $denCumplimiento) * 100, 1)
            : 0.0;

        $denAusentismo = $denCumplimiento + $cancelados;
        $porcAusentismo = $denAusentismo > 0
            ? round(($cancelados / $denAusentismo) * 100, 1)
            : 0.0;

        $turnosReasignar = $cancelados;

        // Tendencia semanal
        $tendencia = $this->modelo->obtenerTendenciaSemanalTurnos($fechaDesde, $fechaHasta);
        $labelsSemanas = [];
        $datosCumplimientoSemanal = [];

        foreach ($tendencia as $row) {
            $totalSem  = (int)($row['totalTurnos'] ?? 0);
            $finSem    = (int)($row['turnosFinalizados'] ?? 0);
            $fechaIni  = $row['fechaInicioSemana'] ?? '';

            // Etiqueta tipo "Sem del 01-06-2025"
            $label = $fechaIni
                ? 'Sem del ' . date('d-m-Y', strtotime($fechaIni))
                : 'Semana';

            $labelsSemanas[] = $label;

            $porcSem = $totalSem > 0
                ? round(($finSem / $totalSem) * 100, 1)
                : 0.0;

            $datosCumplimientoSemanal[] = $porcSem;
        }

        // Solicitudes pendientes
        $solPendientes = $this->modelo->contarSolicitudesPendientes();

        return [
            'fechas' => [
                'desde' => $fechaDesde,
                'hasta' => $fechaHasta,
            ],
            'totalesPorEstado' => $totalesPorEstado,
            'totalTurnos'      => $totalTurnos,
            'kpis' => [
                'cumplimiento'        => $porcCumplimiento,
                'ausentismo'          => $porcAusentismo,
                'turnosReasignar'     => $turnosReasignar,
                'solicitudesPendientes' => $solPendientes,
            ],
            'tendenciaSemanal' => [
                'labels' => $labelsSemanas,
                'datos'  => $datosCumplimientoSemanal,
            ],
        ];
    }
}
