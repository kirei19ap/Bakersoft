<?php

class modeloTurnos
{

    private $PDO;

    public function __construct()
    {
        require_once __DIR__ . ("/../../config/bd.php");
        $con = new bd();
        $this->PDO = $con->conexion();
    }

    /**
     * Devuelve todos los turnos laborales activos.
     * Se usa para llenar el <select> de turnos en la vista.
     */
    public function obtenerTurnosActivos()
    {
        $sql = "
            SELECT idTurno, nombre, horaDesde, horaHasta, descripcion
            FROM turnoslaborales
            WHERE estado = 'Activo'
            ORDER BY horaDesde
        ";

        $stmt = $this->PDO->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve los operarios con la asignación (si existe) para una fecha y turno dados.
     *
     * - Filtra empleados Activos y no eliminados.
     * - Considera operarios aquellos cuyo descrPuesto comienza con 'Operario'
     *   (ajustar el LIKE si en tu BD usan otro texto).
     */
    public function obtenerOperariosConAsignacion($fecha, $idTurno)
    {
        $sql = "
            SELECT 
                e.id_empleado,
                e.legajo,
                e.nombre,
                e.apellido,
                p.descrPuesto,
                e.estado      AS estadoEmpleado,
                a.idAsignacion,
                a.estado      AS estadoAsignacion
            FROM empleados e
            LEFT JOIN asignacionturnos a 
                   ON a.idEmpleado = e.id_empleado
                  AND a.fecha      = :fecha
                  AND a.idTurno    = :idTurno
            LEFT JOIN puesto p 
                   ON p.idPuesto   = e.id_puesto
            WHERE e.estado    = 'Activo'
              AND e.eliminado = 0
              AND p.descrPuesto LIKE 'Operario%'
            ORDER BY e.apellido, e.nombre
        ";

        $stmt = $this->PDO->prepare($sql);
        $stmt->execute([
            ':fecha'   => $fecha,
            ':idTurno' => $idTurno
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Guarda las asignaciones para una fecha y turno.
     *
     * $asignaciones:
     *  [
     *    ['idEmpleado' => 1, 'estado' => 'Asignado' | 'SinTurno'],
     *    ...
     *  ]
     *
     * - 'Asignado': inserta/actualiza la asignación con estado 'Asignado'.
     * - 'SinTurno': elimina cualquier asignación existente para ese empleado/turno/fecha.
     *
     * Valida solapamiento de turnos Asignados/Confirmados en la misma fecha.
     *
     * Devuelve:
     *  [
     *    'ok'        => bool,
     *    'mensaje'   => string,
     *    'conflictos'=> [
     *          ['id_empleado' => ..., 'legajo' => ..., 'nombre' => ...],
     *          ...
     *    ]
     *  ]
     */
    public function guardarAsignaciones($fecha, $idTurno, $asignaciones, $usuarioId)
    {
        // Obtenemos el rango horario del turno actual
        $sqlTurno = "
            SELECT horaDesde, horaHasta
            FROM turnoslaborales
            WHERE idTurno = :idTurno
              AND estado  = 'Activo'
        ";
        $stmtTurno = $this->PDO->prepare($sqlTurno);
        $stmtTurno->execute([':idTurno' => $idTurno]);
        $turno = $stmtTurno->fetch(PDO::FETCH_ASSOC);

        if (!$turno) {
            return [
                'ok'         => false,
                'mensaje'    => 'El turno seleccionado no existe o no está activo.',
                'conflictos' => []
            ];
        }

        $horaDesdeNuevo = $turno['horaDesde'];
        $horaHastaNuevo = $turno['horaHasta'];

        // 1) Validación de solapamientos
        $conflictos = $this->detectarSolapamientos(
            $fecha,
            $idTurno,
            $horaDesdeNuevo,
            $horaHastaNuevo,
            $asignaciones
        );

        if (!empty($conflictos)) {
            return [
                'ok'        => false,
                'mensaje'   => 'Existen empleados con solapamiento de turnos en la fecha seleccionada. Revisá la selección.',
                'conflictos' => $conflictos
            ];
        }

        // 2) Si no hay solapamientos, grabamos todo en una transacción
        try {
            $this->PDO->beginTransaction();

            foreach ($asignaciones as $asig) {
                $idEmpleado = (int)$asig['idEmpleado'];
                $estado     = $asig['estado'];

                if ($estado === 'Asignado') {
                    // Inserta o actualiza (por la UNIQUE KEY (idEmpleado,idTurno,fecha))
                    $sqlUpsert = "
                        INSERT INTO asignacionturnos (idEmpleado, idTurno, fecha, estado, fechaAlta, usuarioAlta)
                        VALUES (:idEmpleado, :idTurno, :fecha, 'Asignado', NOW(), :usuarioAlta)
                        ON DUPLICATE KEY UPDATE
                            estado      = VALUES(estado),
                            fechaAlta   = VALUES(fechaAlta),
                            usuarioAlta = VALUES(usuarioAlta)
                    ";
                    $stmtUpsert = $this->PDO->prepare($sqlUpsert);
                    $stmtUpsert->execute([
                        ':idEmpleado'  => $idEmpleado,
                        ':idTurno'     => $idTurno,
                        ':fecha'       => $fecha,
                        ':usuarioAlta' => $usuarioId
                    ]);
                } elseif ($estado === 'SinTurno') {
                    // Eliminamos cualquier asignación existente para ese empleado/turno/fecha
                    $sqlDelete = "
                        DELETE FROM asignacionturnos
                        WHERE idEmpleado = :idEmpleado
                          AND idTurno    = :idTurno
                          AND fecha      = :fecha
                    ";
                    $stmtDelete = $this->PDO->prepare($sqlDelete);
                    $stmtDelete->execute([
                        ':idEmpleado' => $idEmpleado,
                        ':idTurno'    => $idTurno,
                        ':fecha'      => $fecha
                    ]);
                }

                // En esta pantalla el Admin sólo trabaja con "Asignado" / "SinTurno".
                // Confirmado y Finalizado los manejará el Portal del Empleado.
            }

            $this->PDO->commit();

            return [
                'ok'         => true,
                'mensaje'    => 'Asignaciones guardadas correctamente.',
                'conflictos' => []
            ];
        } catch (Exception $e) {
            $this->PDO->rollBack();
            return [
                'ok'         => false,
                'mensaje'    => 'Error al guardar asignaciones: ' . $e->getMessage(),
                'conflictos' => []
            ];
        }
    }

    /**
     * Detecta solapamientos de turnos para los empleados que se intentan asignar.
     *
     * - Revisa, para cada empleado con estado 'Asignado' en $asignaciones, si en la misma fecha
     *   tiene otro turno (distinto al que se está asignando) con estado Asignado o Confirmado
     *   cuyo rango horario se solape con el turno actual.
     *
     * Devuelve:
     *  [
     *    ['id_empleado' => ..., 'legajo' => ..., 'nombre' => 'Nombre Apellido'],
     *    ...
     *  ]
     */
    private function detectarSolapamientos($fecha, $idTurnoNuevo, $horaDesdeNuevo, $horaHastaNuevo, $asignaciones)
    {
        $conflictos = [];

        // Sólo nos interesan los empledos que se van a dejar en 'Asignado'
        $empleadosAsignados = array_filter($asignaciones, function ($asig) {
            return isset($asig['estado']) && $asig['estado'] === 'Asignado';
        });

        if (empty($empleadosAsignados)) {
            return [];
        }

        $sql = "
            SELECT 
                a.idEmpleado,
                e.legajo,
                e.nombre,
                e.apellido,
                t.horaDesde,
                t.horaHasta
            FROM asignacionturnos a
            JOIN turnoslaborales t ON t.idTurno = a.idTurno
            JOIN empleados e       ON e.id_empleado = a.idEmpleado
            WHERE a.fecha      = :fecha
              AND a.estado IN ('Asignado','Confirmado')
              AND a.idTurno <> :idTurnoNuevo
              AND a.idEmpleado = :idEmpleado
        ";
        $stmt = $this->PDO->prepare($sql);

        foreach ($empleadosAsignados as $asig) {
            $idEmpleado = (int)$asig['idEmpleado'];

            $stmt->execute([
                ':fecha'       => $fecha,
                ':idTurnoNuevo' => $idTurnoNuevo,
                ':idEmpleado'  => $idEmpleado
            ]);

            $turnosExistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($turnosExistentes as $row) {
                $desdeExistente = $row['horaDesde'];
                $hastaExistente = $row['horaHasta'];

                // [A1, A2] y [B1, B2] se solapan si:
                // A1 < B2 AND A2 > B1
                $solapan = ($desdeExistente < $horaHastaNuevo) && ($hastaExistente > $horaDesdeNuevo);

                if ($solapan) {
                    $conflictos[] = [
                        'id_empleado' => $row['idEmpleado'],
                        'legajo'      => $row['legajo'],
                        'nombre'      => trim($row['nombre'] . ' ' . $row['apellido'])
                    ];
                    // Con un conflicto ya alcanza; pasamos al siguiente empleado
                    break;
                }
            }
        }

        return $conflictos;
    }

    /**
     * Listado detallado de asignaciones de turnos.
     *
     * Filtros opcionales:
     *  - $fechaDesde, $fechaHasta (Y-m-d)
     *  - $idTurno (int)
     *  - $estado ('Asignado', 'Confirmado', 'Finalizado')
     */
    public function obtenerAsignacionesDetalle($fechaDesde = null, $fechaHasta = null, $idTurno = null, $estado = null)
    {
        $sql = "
            SELECT
                a.idAsignacion,
                a.fecha,
                a.idTurno,
                a.estado          AS estadoAsignacion,
                t.nombre          AS nombreTurno,
                t.horaDesde,
                t.horaHasta,
                e.id_empleado,
                e.legajo,
                e.nombre,
                e.apellido,
                p.descrPuesto,
                e.estado          AS estadoEmpleado
            FROM asignacionturnos a
            JOIN turnoslaborales t ON t.idTurno      = a.idTurno
            JOIN empleados      e ON e.id_empleado   = a.idEmpleado
            LEFT JOIN puesto    p ON p.idPuesto      = e.id_puesto
            WHERE 1 = 1
        ";

        $params = [];

        if (!empty($fechaDesde)) {
            $sql .= " AND a.fecha >= :fechaDesde";
            $params[':fechaDesde'] = $fechaDesde;
        }

        if (!empty($fechaHasta)) {
            $sql .= " AND a.fecha <= :fechaHasta";
            $params[':fechaHasta'] = $fechaHasta;
        }

        if (!empty($idTurno)) {
            $sql .= " AND a.idTurno = :idTurno";
            $params[':idTurno'] = (int)$idTurno;
        }

        if (!empty($estado)) {
            $sql .= " AND a.estado = :estado";
            $params[':estado'] = $estado;
        }

        $sql .= "
            ORDER BY 
                a.fecha ASC,
                t.horaDesde ASC,
                e.apellido ASC,
                e.nombre ASC
        ";

        $stmt = $this->PDO->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una asignación puntual por ID, con detalle de turno y empleado.
     */
    public function obtenerAsignacionPorId($idAsignacion)
    {
        $sql = "
            SELECT
                a.idAsignacion,
                a.fecha,
                a.idTurno,
                a.estado       AS estadoAsignacion,
                t.nombre       AS nombreTurno,
                t.horaDesde,
                t.horaHasta,
                e.id_empleado,
                e.legajo,
                e.nombre,
                e.apellido,
                e.estado       AS estadoEmpleado,
                p.descrPuesto
            FROM asignacionturnos a
            JOIN turnoslaborales t ON t.idTurno    = a.idTurno
            JOIN empleados      e ON e.id_empleado = a.idEmpleado
            LEFT JOIN puesto    p ON p.idPuesto    = e.id_puesto
            WHERE a.idAsignacion = :idAsignacion
        ";

        $stmt = $this->PDO->prepare($sql);
        $stmt->execute([':idAsignacion' => $idAsignacion]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerAsignacionDeEmpleado($idAsignacion, $idEmpleado)
    {
        $sql = "
        SELECT 
            a.*,
            t.nombre    AS nombreTurno,
            t.horaDesde,
            t.horaHasta
        FROM asignacionturnos a
        JOIN turnoslaborales t ON t.idTurno = a.idTurno
        WHERE a.idAsignacion = :idAsignacion
          AND a.idEmpleado   = :idEmpleado
        LIMIT 1
    ";

        $stmt = $this->PDO->prepare($sql);
        $stmt->execute([
            ':idAsignacion' => $idAsignacion,
            ':idEmpleado'   => $idEmpleado
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC); // false si no hay fila
    }

    public function obtenerSolicitudesTurno($estado = 'Pendiente')
    {
        $sql = "
        SELECT 
            s.idSolicitud,
            s.idAsignacion,
            s.idEmpleado,
            s.tipo,
            s.motivo,
            s.estado,
            s.fechaSolicitud,
            s.fechaRespuesta,
            s.usuarioRespuesta,
            a.fecha,
            a.estado AS estadoAsignacion,
            t.idTurno,
            t.nombre     AS nombreTurno,
            t.horaDesde,
            t.horaHasta,
            e.legajo,
            e.nombre     AS nombreEmpleado,
            e.apellido   AS apellidoEmpleado
        FROM solicitudes_turno s
        JOIN asignacionturnos a ON a.idAsignacion = s.idAsignacion
        JOIN turnoslaborales t  ON t.idTurno      = a.idTurno
        JOIN empleados e        ON e.id_empleado  = s.idEmpleado
        WHERE 1=1
    ";

        $params = [];

        if ($estado !== null && $estado !== '') {
            $sql .= " AND s.estado = :estado";
            $params[':estado'] = $estado;
        }

        $sql .= " ORDER BY s.fechaSolicitud DESC";

        $stmt = $this->PDO->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSolicitudPorId($idSolicitud)
    {
        $sql = "
        SELECT 
            s.*,
            a.fecha,
            a.estado AS estadoAsignacion,
            a.idEmpleado,
            t.nombre   AS nombreTurno,
            t.horaDesde,
            t.horaHasta
        FROM solicitudes_turno s
        JOIN asignacionturnos a ON a.idAsignacion = s.idAsignacion
        JOIN turnoslaborales t  ON t.idTurno      = a.idTurno
        WHERE s.idSolicitud = :id
        LIMIT 1
    ";

        $stmt = $this->PDO->prepare($sql);
        $stmt->execute([':id' => $idSolicitud]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarEstadoSolicitud($idSolicitud, $estado, $usuarioRespuesta = null)
    {
        $sql = "
        UPDATE solicitudes_turno
        SET estado = :estado,
            fechaRespuesta = NOW(),
            usuarioRespuesta = :usuarioRespuesta
        WHERE idSolicitud = :idSolicitud
    ";

        $stmt = $this->PDO->prepare($sql);
        return $stmt->execute([
            ':estado'          => $estado,
            ':usuarioRespuesta' => $usuarioRespuesta,
            ':idSolicitud'     => $idSolicitud
        ]);
    }

    public function actualizarEstadoAsignacion($idAsignacion, $nuevoEstado)
    {
        $sql = "
        UPDATE asignacionturnos
        SET estado = :estado
        WHERE idAsignacion = :idAsignacion
    ";

        $stmt = $this->PDO->prepare($sql);
        return $stmt->execute([
            ':estado'       => $nuevoEstado,
            ':idAsignacion' => $idAsignacion
        ]);
    }



    /**
     * Elimina una asignación por ID.
     * Podés ajustar la regla para no eliminar Finalizados si querés.
     */
    public function eliminarAsignacion($idAsignacion)
    {
        // Opcional: validamos estado antes de borrar
        $asig = $this->obtenerAsignacionPorId($idAsignacion);
        if (!$asig) {
            return [
                'ok'      => false,
                'mensaje' => 'La asignación indicada no existe.'
            ];
        }

        // Ejemplo: no permitir borrar Finalizados
        if ($asig['estadoAsignacion'] === 'Finalizado') {
            return [
                'ok'      => false,
                'mensaje' => 'No se puede eliminar un turno finalizado.'
            ];
        }

        $sql = "DELETE FROM asignacionturnos WHERE idAsignacion = :idAsignacion";
        $stmt = $this->PDO->prepare($sql);
        $stmt->execute([':idAsignacion' => $idAsignacion]);

        return [
            'ok'      => true,
            'mensaje' => 'Asignación eliminada correctamente.'
        ];
    }

    /**
     * Devuelve datos necesarios para el modal de reasignación:
     * - Asignación actual
     * - Operarios de ese turno/día (con su estado para esa fecha/turno)
     */
    public function obtenerDatosReasignacion($idAsignacion)
    {
        $asig = $this->obtenerAsignacionPorId($idAsignacion);
        if (!$asig) {
            return [
                'ok'      => false,
                'mensaje' => 'La asignación indicada no existe.'
            ];
        }

        $fecha   = $asig['fecha'];
        $idTurno = $asig['idTurno'];

        // Reutilizamos el método existente para obtener operarios de ese turno y fecha
        $operarios = $this->obtenerOperariosConAsignacion($fecha, $idTurno);

        return [
            'ok'        => true,
            'mensaje'   => '',
            'asignacion' => $asig,
            'operarios' => $operarios
        ];
    }

    /**
     * Reasigna una asignación a otro empleado.
     * Verifica solapamientos para el nuevo empleado.
     */
    public function reasignarAsignacion($idAsignacion, $idEmpleadoNuevo, $usuarioId)
    {
        // Obtenemos la asignación actual y el turno
        $asig = $this->obtenerAsignacionPorId($idAsignacion);
        if (!$asig) {
            return [
                'ok'      => false,
                'mensaje' => 'La asignación indicada no existe.'
            ];
        }

        $fecha         = $asig['fecha'];
        $idTurno       = $asig['idTurno'];
        $horaDesdeNuevo = $asig['horaDesde'];
        $horaHastaNuevo = $asig['horaHasta'];

        // Buscamos otros turnos del nuevo empleado ese mismo día
        $sql = "
            SELECT
                a.idAsignacion,
                t.horaDesde,
                t.horaHasta,
                e.legajo,
                e.nombre,
                e.apellido
            FROM asignacionturnos a
            JOIN turnoslaborales t ON t.idTurno    = a.idTurno
            JOIN empleados      e ON e.id_empleado = a.idEmpleado
            WHERE a.fecha      = :fecha
              AND a.estado IN ('Asignado','Confirmado')
              AND a.idEmpleado = :idEmpleadoNuevo
              AND a.idAsignacion <> :idAsignacion
        ";

        $stmt = $this->PDO->prepare($sql);
        $stmt->execute([
            ':fecha'          => $fecha,
            ':idEmpleadoNuevo' => $idEmpleadoNuevo,
            ':idAsignacion'   => $idAsignacion
        ]);

        $conflictos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $desdeExistente = $row['horaDesde'];
            $hastaExistente = $row['horaHasta'];

            $solapan = ($desdeExistente < $horaHastaNuevo) && ($hastaExistente > $horaDesdeNuevo);
            if ($solapan) {
                $conflictos[] = [
                    'id_empleado' => $idEmpleadoNuevo,
                    'legajo'      => $row['legajo'],
                    'nombre'      => trim($row['nombre'] . ' ' . $row['apellido'])
                ];
                break;
            }
        }

        if (!empty($conflictos)) {
            return [
                'ok'        => false,
                'mensaje'   => 'El empleado seleccionado tiene otro turno solapado ese día.',
                'conflictos' => $conflictos
            ];
        }

        // Si no hay solapamiento, actualizamos la asignación
        $sqlUpdate = "
            UPDATE asignacionturnos
            SET idEmpleado = :idEmpleadoNuevo,
                fechaAlta  = NOW(),
                usuarioAlta= :usuarioId
            WHERE idAsignacion = :idAsignacion
        ";

        $stmtUp = $this->PDO->prepare($sqlUpdate);
        $stmtUp->execute([
            ':idEmpleadoNuevo' => $idEmpleadoNuevo,
            ':usuarioId'       => $usuarioId,
            ':idAsignacion'    => $idAsignacion
        ]);

        return [
            'ok'      => true,
            'mensaje' => 'Turno reasignado correctamente.'
        ];
    }

    /**
     * Lista los turnos de un empleado en un rango de fechas.
     */
    public function obtenerTurnosDeEmpleado($idEmpleado, $fechaDesde, $fechaHasta, $estado = null)
    {
        $sql = "
            SELECT
                a.idAsignacion,
                a.fecha,
                a.estado      AS estadoAsignacion,
                t.idTurno,
                t.nombre      AS nombreTurno,
                t.horaDesde,
                t.horaHasta,
                (
                SELECT COUNT(*) 
                FROM solicitudes_turno s
                WHERE s.idAsignacion = a.idAsignacion
                  AND s.idEmpleado   = a.idEmpleado
                  AND s.estado       = 'Pendiente'
            ) AS solicitudesPendientes
            FROM asignacionturnos a
            JOIN turnoslaborales t ON t.idTurno = a.idTurno
            WHERE a.idEmpleado = :idEmpleado
              AND a.fecha BETWEEN :fechaDesde AND :fechaHasta
              AND a.estado <> 'Cancelado'
        ";

        $params = [
            ':idEmpleado'  => $idEmpleado,
            ':fechaDesde'  => $fechaDesde,
            ':fechaHasta'  => $fechaHasta,
        ];

        if ($estado !== null) {
            $sql .= " AND a.estado = :estado";
            $params[':estado'] = $estado;
        }

        $sql .= "
            ORDER BY 
                a.fecha ASC,
                t.horaDesde ASC
        ";

        $stmt = $this->PDO->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cambia el estado de un turno, validando que pertenezca al empleado.
     */
    public function cambiarEstadoTurnoEmpleado($idAsignacion, $idEmpleado, $nuevoEstado, $usuarioId)
    {
        // Obtenemos la asignación
        $sql = "
            SELECT 
                a.idAsignacion,
                a.idEmpleado,
                a.fecha,
                a.estado AS estadoActual
            FROM asignacionturnos a
            WHERE a.idAsignacion = :idAsignacion
        ";

        $stmt = $this->PDO->prepare($sql);
        $stmt->execute([':idAsignacion' => $idAsignacion]);
        $asig = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$asig) {
            return [
                'ok'      => false,
                'mensaje' => 'La asignación indicada no existe.'
            ];
        }

        if ((int)$asig['idEmpleado'] !== (int)$idEmpleado) {
            return [
                'ok'      => false,
                'mensaje' => 'El turno no pertenece al empleado logueado.'
            ];
        }

        $estadoActual = $asig['estadoActual'];

        // Validamos transición
        $transicionValida = false;
        if ($estadoActual === 'Asignado' && $nuevoEstado === 'Confirmado') {
            $transicionValida = true;
        } elseif ($estadoActual === 'Confirmado' && $nuevoEstado === 'Finalizado') {
            $transicionValida = true;
        }

        if (!$transicionValida) {
            return [
                'ok'      => false,
                'mensaje' => 'No es posible cambiar el estado desde ' . $estadoActual . ' a ' . $nuevoEstado . '.'
            ];
        }

        // Si quiere finalizar, controlamos que la fecha del turno no sea futura
        if ($nuevoEstado === 'Finalizado') {
            $hoy   = date('Y-m-d');
            $fecha = $asig['fecha'];

            if ($fecha > $hoy) {
                return [
                    'ok'      => false,
                    'mensaje' => 'No se puede finalizar un turno futuro.'
                ];
            }
        }

        // Actualizamos estado
        $sqlUp = "
            UPDATE asignacionturnos
            SET estado     = :nuevoEstado,
                fechaAlta  = NOW(),
                usuarioAlta= :usuarioId
            WHERE idAsignacion = :idAsignacion
        ";

        $stmtUp = $this->PDO->prepare($sqlUp);
        $stmtUp->execute([
            ':nuevoEstado'   => $nuevoEstado,
            ':usuarioId'     => $usuarioId,
            ':idAsignacion'  => $idAsignacion
        ]);

        return [
            'ok'      => true,
            'mensaje' => 'Estado del turno actualizado correctamente.'
        ];
    }

    public function obtenerEmpleadoPorUsuarioId($usuarioLogin)
    {
        // $usuarioLogin es lo que viene de $_SESSION['user'], ej: "dbaldomir"
        $sql = "
        SELECT 
            e.id_empleado,
            e.nombre,
            e.apellido
        FROM empleados e
        JOIN usuarios u 
            ON u.id = e.usuario_id
        WHERE u.usuario = :usuarioLogin
          AND e.eliminado = 0
        LIMIT 1
    ";

        $stmt = $this->PDO->prepare($sql);
        $stmt->execute([':usuarioLogin' => $usuarioLogin]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function contarTurnosPendientesEmpleado($idEmpleado, $fechaDesde, $fechaHasta)
    {
        $sql = "
            SELECT COUNT(*) AS pendientes
            FROM asignacionturnos
            WHERE idEmpleado = :idEmpleado
              AND estado = 'Asignado'
              AND fecha BETWEEN :desde AND :hasta
        ";

        $stmt = $this->PDO->prepare($sql);
        $stmt->execute([
            ':idEmpleado' => $idEmpleado,
            ':desde'      => $fechaDesde,
            ':hasta'      => $fechaHasta,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['pendientes'] ?? 0);
    }

    public function obtenerResumenAsignacionesPorFecha($fechaDesde, $fechaHasta)
    {
        $sql = "
            SELECT 
                a.fecha,
                t.idTurno,
                t.nombre      AS nombreTurno,
                t.horaDesde,
                t.horaHasta,
                COUNT(*)      AS cantidad
            FROM asignacionturnos a
            JOIN turnoslaborales t ON t.idTurno = a.idTurno
            WHERE a.fecha BETWEEN :desde AND :hasta
            GROUP BY a.fecha, t.idTurno, t.nombre, t.horaDesde, t.horaHasta
            ORDER BY a.fecha ASC, t.horaDesde ASC
        ";

        $stmt = $this->PDO->prepare($sql);
        $stmt->execute([
            ':desde' => $fechaDesde,
            ':hasta' => $fechaHasta
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function tieneSolicitudPendiente($idAsignacion, $idEmpleado)
    {
        $sql = "
        SELECT COUNT(*) AS cant
        FROM solicitudes_turno
        WHERE idAsignacion = :idAsignacion
          AND idEmpleado   = :idEmpleado
          AND estado       = 'Pendiente'
    ";
        $stmt = $this->PDO->prepare($sql);
        $stmt->execute([
            ':idAsignacion' => $idAsignacion,
            ':idEmpleado'   => $idEmpleado
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['cant'] ?? 0) > 0;
    }

    public function crearSolicitudTurno($idAsignacion, $idEmpleado, $tipo, $motivo)
    {
        $sql = "
        INSERT INTO solicitudes_turno (idAsignacion, idEmpleado, tipo, motivo)
        VALUES (:idAsignacion, :idEmpleado, :tipo, :motivo)
    ";
        $stmt = $this->PDO->prepare($sql);
        return $stmt->execute([
            ':idAsignacion' => $idAsignacion,
            ':idEmpleado'   => $idEmpleado,
            ':tipo'         => $tipo,
            ':motivo'       => $motivo,
        ]);
    }

    public function contarSolicitudesPendientes()
    {
        $sql = "
        SELECT COUNT(*) AS cant
        FROM solicitudes_turno
        WHERE estado = 'Pendiente'
    ";
        $stmt = $this->PDO->query($sql);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)($row['cant'] ?? 0);
    }
}
