<?php
class ModeloLicencias
{
    private $PDO;
    private array $cacheEstados = [];
    public function __construct()
    {
        require_once("../../config/bd.php");
        $con = new bd();
        $this->PDO = $con->conexion();
    }

    private function estadosPermitenEdicion(): array
    {
        // Estados válidos por ID, según tu tabla estados_licencia:
        // 2 = Pendiente de envío, 3 = Pendiente de aprobación
        return [2, 3];
    }
    /* ===================== EMPLEADO ===================== */
    public function empleadoPorUsuarioId(int $usuarioId): ?array
    {
        $sql = "
            SELECT 
                e.id_empleado,
                e.fecha_ingreso,
                e.nombre AS emp_nombre,
                e.apellido AS emp_apellido
            FROM usuarios u
            LEFT JOIN empleados e ON e.usuario_id = u.id
            WHERE u.id = :uid
            LIMIT 1;
        ";
        $st = $this->PDO->prepare($sql);
        $st->execute([':uid' => $usuarioId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function empleadoPorLogin(string $userLogin): ?array
    {
        $sql = "
        SELECT 
            e.id_empleado,
            e.fecha_ingreso,
            e.nombre   AS emp_nombre,
            e.apellido AS emp_apellido
        FROM usuarios u
        LEFT JOIN empleados e ON e.usuario_id = u.id
        WHERE u.usuario = :ulogin
        LIMIT 1;
    ";
        $st = $this->PDO->prepare($sql);
        $st->execute([':ulogin' => $userLogin]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /* ===================== CATÁLOGOS ===================== */
    public function listarTipos(): array
    {
        $sql = "SELECT id_tipo, descripcion FROM tipos_licencia ORDER BY descripcion";
        return $this->PDO->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function estadoIdPorNombre(string $nombre): ?int
    {
        if (isset($this->cacheEstados[$nombre])) {
            return $this->cacheEstados[$nombre];
        }
        $sql = "SELECT id_estado FROM estados_licencia WHERE nombre = :n LIMIT 1";
        $st  = $this->PDO->prepare($sql);
        $st->execute([':n' => $nombre]);
        $id = $st->fetchColumn();
        $this->cacheEstados[$nombre] = $id ? (int)$id : null;
        return $this->cacheEstados[$nombre];
    }

    public function licenciaBasicaPorId(int $idLicencia): ?array
    {
        $sql = "
        SELECT l.id_licencia, l.id_empleado, l.id_estado
        FROM licencia l
        WHERE l.id_licencia = :id
        LIMIT 1
    ";
        $st = $this->PDO->prepare($sql);
        $st->execute([':id' => $idLicencia]);
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function diasTomadosAnio(int $idEmpleado, int $anio): int
    {
        $desde = "{$anio}-01-01";
        $hasta = "{$anio}-12-31";

        $sqlConImpacto = "
        SELECT COALESCE(SUM(d.cantidad_dias),0) AS total
          FROM licencia l
          JOIN detalle_licencia d  ON d.id_licencia = l.id_licencia
          JOIN tipos_licencia tl   ON tl.id_tipo = l.id_tipo
         WHERE l.id_empleado = :emp
           AND l.id_estado = 5  -- Aprobada (por ID)
           AND tl.impacta_banco_vacaciones = 1
           AND (
                 d.fecha_inicio BETWEEN :desde AND :hasta
              OR d.fecha_fin    BETWEEN :desde AND :hasta
              OR (:desde <= d.fecha_inicio AND :hasta >= d.fecha_fin)
           )
    ";

        $sqlSinImpacto = "
        SELECT COALESCE(SUM(d.cantidad_dias),0) AS total
          FROM licencia l
          JOIN detalle_licencia d  ON d.id_licencia = l.id_licencia
         WHERE l.id_empleado = :emp
           AND l.id_estado = 5  -- Aprobada (por ID)
           AND (
                 d.fecha_inicio BETWEEN :desde AND :hasta
              OR d.fecha_fin    BETWEEN :desde AND :hasta
              OR (:desde <= d.fecha_inicio AND :hasta >= d.fecha_fin)
           )
    ";

        try {
            $st = $this->PDO->prepare($sqlConImpacto);
            $st->execute([':emp' => $idEmpleado, ':desde' => $desde, ':hasta' => $hasta]);
            return (int)($st->fetchColumn() ?: 0);
        } catch (\Throwable $e) {
            $st = $this->PDO->prepare($sqlSinImpacto);
            $st->execute([':emp' => $idEmpleado, ':desde' => $desde, ':hasta' => $hasta]);
            return (int)($st->fetchColumn() ?: 0);
        }
    }


    public function diasPendientesAnio(int $idEmpleado, int $anio): int
    {
        $desde = "{$anio}-01-01";
        $hasta = "{$anio}-12-31";

        $sqlConImpacto = "
        SELECT COALESCE(SUM(d.cantidad_dias),0) AS total
          FROM licencia l
          JOIN detalle_licencia d  ON d.id_licencia = l.id_licencia
          JOIN tipos_licencia tl   ON tl.id_tipo = l.id_tipo
         WHERE l.id_empleado = :emp
           AND l.id_estado IN (2,3)   -- 2=Pend. de envío, 3=Pend. de aprobación
           AND tl.impacta_banco_vacaciones = 1
           AND (
                 d.fecha_inicio BETWEEN :desde AND :hasta
              OR d.fecha_fin    BETWEEN :desde AND :hasta
              OR (:desde <= d.fecha_inicio AND :hasta >= d.fecha_fin)
           )
    ";

        $sqlSinImpacto = "
        SELECT COALESCE(SUM(d.cantidad_dias),0) AS total
          FROM licencia l
          JOIN detalle_licencia d  ON d.id_licencia = l.id_licencia
         WHERE l.id_empleado = :emp
           AND l.id_estado IN (2,3)
           AND (
                 d.fecha_inicio BETWEEN :desde AND :hasta
              OR d.fecha_fin    BETWEEN :desde AND :hasta
              OR (:desde <= d.fecha_inicio AND :hasta >= d.fecha_fin)
           )
    ";


        $st = $this->PDO->prepare($sqlSinImpacto);
        $st->execute([':emp' => $idEmpleado, ':desde' => $desde, ':hasta' => $hasta]);
        return (int)($st->fetchColumn() ?: 0);
    }

    /** Total de días aprobados del año (todas las licencias aprobadas, no sólo vacaciones) */
    public function diasAprobadosAnioTotal(int $idEmpleado, int $anio): int
    {
        $desde = "{$anio}-01-01";
        $hasta = "{$anio}-12-31";
        $sql = "
        SELECT COALESCE(SUM(d.cantidad_dias),0)
          FROM licencia l
          JOIN detalle_licencia d ON d.id_licencia = l.id_licencia
         WHERE l.id_empleado = :emp
           AND l.id_estado   = 5
           AND (
                d.fecha_inicio BETWEEN :desde AND :hasta
             OR d.fecha_fin    BETWEEN :desde AND :hasta
             OR (:desde <= d.fecha_inicio AND :hasta >= d.fecha_fin)
           )
    ";
        $st = $this->PDO->prepare($sql);
        $st->execute([':emp' => $idEmpleado, ':desde' => $desde, ':hasta' => $hasta]);
        return (int)$st->fetchColumn();
    }

    /** Días aprobados del año que impactan vacaciones (usa el flag de tipos_licencia) */
    public function diasAprobadosVacacionesAnio(int $idEmpleado, int $anio): int
    {
        $desde = "{$anio}-01-01";
        $hasta = "{$anio}-12-31";
        $sql = "
        SELECT COALESCE(SUM(d.cantidad_dias),0)
          FROM licencia l
          JOIN detalle_licencia d ON d.id_licencia = l.id_licencia
          JOIN tipos_licencia tl  ON tl.id_tipo    = l.id_tipo
         WHERE l.id_empleado = :emp
           AND l.id_estado   = 5
           AND tl.impacta_banco_vacaciones = 1
           AND (
                d.fecha_inicio BETWEEN :desde AND :hasta
             OR d.fecha_fin    BETWEEN :desde AND :hasta
             OR (:desde <= d.fecha_inicio AND :hasta >= d.fecha_fin)
           )
    ";
        try {
            $st = $this->PDO->prepare($sql);
            $st->execute([':emp' => $idEmpleado, ':desde' => $desde, ':hasta' => $hasta]);
            return (int)$st->fetchColumn();
        } catch (\Throwable $e) {
            // Si la columna no existe, devolvemos 0 (el total quedará todo como “otras”)
            return 0;
        }
    }


    /* ===================== LISTADO ===================== */
    public function listarSolicitudes(int $idEmpleado): array
    {
        $sql = "
            SELECT
              l.id_licencia,
              tl.descripcion AS tipo,
              el.nombre      AS estado,
              d.fecha_inicio,
              d.fecha_fin,
              d.cantidad_dias,
              l.fecha_solicitud
            FROM licencia l
            JOIN detalle_licencia d  ON d.id_licencia = l.id_licencia
            JOIN tipos_licencia tl   ON tl.id_tipo = l.id_tipo
            JOIN estados_licencia el ON el.id_estado = l.id_estado
            WHERE l.id_empleado = :emp
            ORDER BY l.fecha_solicitud DESC, l.id_licencia DESC;
        ";
        $st = $this->PDO->prepare($sql);
        $st->execute([':emp' => $idEmpleado]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ===================== INSERCIÓN ===================== */
    public function crearLicenciaConDetalle(int $idEmpleado, int $idTipo, int $idEstado, string $observaciones, string $fechaInicio, int $cantidadDias, bool $esEnvio): int
    {
        $this->PDO->beginTransaction();
        try {
            $sqlL = "INSERT INTO licencia (id_empleado, id_tipo, id_estado, observaciones, fecha_envio)
                     VALUES (:emp, :tipo, :est, :obs, :fenvio)";
            $stL = $this->PDO->prepare($sqlL);
            $stL->execute([
                ':emp'   => $idEmpleado,
                ':tipo'  => $idTipo,
                ':est'   => $idEstado,
                ':obs'   => ($observaciones !== '' ? $observaciones : null),
                ':fenvio' => $esEnvio ? date('Y-m-d H:i:s') : null,
            ]);
            $idLic = (int)$this->PDO->lastInsertId();

            $fechaFin = date('Y-m-d', strtotime($fechaInicio . ' + ' . max(0, $cantidadDias - 1) . ' day'));
            $sqlD = "INSERT INTO detalle_licencia (id_licencia, fecha_inicio, fecha_fin, cantidad_dias)
                     VALUES (:lic, :fi, :ff, :cant)";
            $stD = $this->PDO->prepare($sqlD);
            $stD->execute([
                ':lic'  => $idLic,
                ':fi'   => $fechaInicio,
                ':ff'   => $fechaFin,
                ':cant' => $cantidadDias
            ]);

            $this->PDO->commit();
            return $idLic;
        } catch (Throwable $e) {
            $this->PDO->rollBack();
            throw $e;
        }
    }

    /* ===================== VALIDACIONES ===================== */
    public function existeSolape(int $idEmpleado, string $fechaInicio, string $fechaFin): bool
    {
        $sql = "
            SELECT 1
            FROM licencia l
            JOIN detalle_licencia d ON d.id_licencia = l.id_licencia
            JOIN estados_licencia e ON e.id_estado = l.id_estado
            WHERE l.id_empleado = :emp
              AND e.nombre IN ('Aprobada','Pendiente de aprobación')
              AND d.fecha_inicio <= :ff
              AND d.fecha_fin    >= :fi
            LIMIT 1;
        ";
        $st = $this->PDO->prepare($sql);
        $st->execute([':emp' => $idEmpleado, ':fi' => $fechaInicio, ':ff' => $fechaFin]);
        return (bool)$st->fetchColumn();
    }





    public function listarPendientesRRHH(): array
    {
        // ID 3 = Pendiente de aprobación
        $sql = "
      SELECT 
        l.id_licencia,
        l.id_empleado,
        l.id_estado               AS id_estado,
        e.nombre                  AS estado,
        CONCAT(emp.apellido, ', ', emp.nombre) AS empleado,
        tl.descripcion            AS tipo,
        d.fecha_inicio,
        d.fecha_fin,
        d.cantidad_dias,
        l.fecha_envio             AS fecha_solicitud
      FROM licencia l
      JOIN empleados emp        ON emp.id_empleado   = l.id_empleado
      JOIN detalle_licencia d   ON d.id_licencia     = l.id_licencia
      JOIN tipos_licencia tl    ON tl.id_tipo        = l.id_tipo
      JOIN estados_licencia e   ON e.id_estado       = l.id_estado
      WHERE l.id_estado = 3
      ORDER BY l.fecha_envio ASC, l.id_licencia ASC
    ";
        return $this->PDO->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }



    public function rrhhActualizarEstado(int $idLicencia, int $nuevoEstadoId, ?string $observacion): bool
    {
        if ($nuevoEstadoId === 6) { // Rechazada
            $sql = "UPDATE licencia 
                   SET id_estado = :est,
                       observaciones = CONCAT(
                          COALESCE(observaciones, ''),
                          CASE WHEN COALESCE(observaciones, '') <> '' THEN ' | ' ELSE '' END,
                          'RRHH: ', :obs
                       )
                 WHERE id_licencia = :id";
            $st = $this->PDO->prepare($sql);
            return $st->execute([
                ':est' => $nuevoEstadoId,
                ':obs' => trim((string)$observacion),
                ':id'  => $idLicencia
            ]);
        } else {
            $sql = "UPDATE licencia 
                   SET id_estado = :est
                 WHERE id_licencia = :id";
            $st = $this->PDO->prepare($sql);
            return $st->execute([
                ':est' => $nuevoEstadoId,
                ':id'  => $idLicencia
            ]);
        }
    }



    public function detalleLicencia(int $idLicencia)
    {
        $sql = "
      SELECT
        l.id_licencia,
        l.id_empleado,
        l.id_estado                 AS id_estado,
        e.nombre                    AS estado,             -- <- TEXTO DEL ESTADO
        l.id_tipo,
        tl.descripcion              AS tipo,
        d.fecha_inicio,
        d.fecha_fin,
        d.cantidad_dias,
        l.fecha_envio               AS fecha_solicitud,
        l.observaciones,
        emp.legajo,
        CONCAT(emp.apellido, ', ', emp.nombre) AS empleado
      FROM licencia l
      JOIN detalle_licencia d   ON d.id_licencia   = l.id_licencia
      JOIN empleados emp        ON emp.id_empleado = l.id_empleado
      JOIN tipos_licencia tl    ON tl.id_tipo      = l.id_tipo
      JOIN estados_licencia e   ON e.id_estado     = l.id_estado
      WHERE l.id_licencia = :id
      LIMIT 1
    ";
        $st = $this->PDO->prepare($sql);
        $st->execute([':id' => $idLicencia]);
        return $st->fetch(PDO::FETCH_ASSOC) ?: null;
    }



    public function cancelarSiProcede(int $idLicencia, int $idEmpleado): bool
    {
        // Estados por ID (según tu tabla)
        $ID_PEND_ENVIO = $this->estadoIdPorNombre('Pendiente de envío');
        $ID_PEND_APROB = $this->estadoIdPorNombre('Pendiente de aprobación');
        $ID_CANCELADA  = $this->estadoIdPorNombre('Cancelada');

        if (!$ID_PEND_ENVIO || !$ID_PEND_APROB || !$ID_CANCELADA) {
            return false;
        }
        // Estado actual de esa licencia del empleado
        $sql = "SELECT l.id_estado
              FROM licencia l
             WHERE l.id_licencia = :id AND l.id_empleado = :emp
             LIMIT 1";
        $st = $this->PDO->prepare($sql);
        $st->execute([':id' => $idLicencia, ':emp' => $idEmpleado]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) return false;

        $idEstadoActual = (int)$row['id_estado'];

        // Permitimos cancelar si está en Pendiente de envío o Pendiente de aprobación
        if ($idEstadoActual !== $ID_PEND_ENVIO && $idEstadoActual !== $ID_PEND_APROB) {
            return false;
        }

        $up = $this->PDO->prepare("
        UPDATE licencia
           SET id_estado = :est
         WHERE id_licencia = :id AND id_empleado = :emp
    ");
        return $up->execute([':est' => $ID_CANCELADA, ':id' => $idLicencia, ':emp' => $idEmpleado]);
    }



    public function enviarSiProcede(int $idLicencia, int $idEmpleado): bool
    {
        $ID_PEND_ENVIO = $this->estadoIdPorNombre('Pendiente de envío');
        $ID_PEND_APROB = $this->estadoIdPorNombre('Pendiente de aprobación');
        $ID_CANCELADA  = $this->estadoIdPorNombre('Cancelada');

        if (!$ID_PEND_ENVIO || !$ID_PEND_APROB || !$ID_CANCELADA) {
            return false;
        }
        $sql = "SELECT l.id_estado
              FROM licencia l
             WHERE l.id_licencia = :id AND l.id_empleado = :emp
             LIMIT 1";
        $st = $this->PDO->prepare($sql);
        $st->execute([':id' => $idLicencia, ':emp' => $idEmpleado]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) return false;

        $idEstadoActual = (int)$row['id_estado'];

        // Sólo se puede ENVIAR si está en 'Pendiente de envío' (2)
        if ($idEstadoActual !== $ID_PEND_ENVIO) return false;

        $up = $this->PDO->prepare("
        UPDATE licencia
           SET id_estado  = :est,
               fecha_envio = NOW()
         WHERE id_licencia = :id AND id_empleado = :emp
    ");
        return $up->execute([':est' => $ID_PEND_APROB, ':id' => $idLicencia, ':emp' => $idEmpleado]);
    }

    /** Empleado puede editar si es dueño y el estado es 2 o 3 */
    public function puedeEditar(int $idLicencia, int $idEmpleado): bool
    {
        // Resolvé una sola vez los IDs reales desde la tabla (los nombres no se usan en el WHERE)
        $idPendEnv   = $this->estadoIdPorNombre('Pendiente de envío');
        $idPendAprob = $this->estadoIdPorNombre('Pendiente de aprobación');

        // Si por algún motivo no existen, no autorizamos
        if (!$idPendEnv || !$idPendAprob) {
            return false;
        }

        // Comparación por ID (sin números mágicos)
        $sql = "
        SELECT COUNT(*)
          FROM licencia
         WHERE id_licencia = ?
           AND id_empleado = ?
           AND id_estado IN (?,?)
    ";
        $st = $this->PDO->prepare($sql);
        $st->execute([$idLicencia, $idEmpleado, $idPendEnv, $idPendAprob]);
        return (bool)$st->fetchColumn();
    }

    public function reporteLicenciasPorPeriodo(string $desde, string $hasta): array
    {
        // Orden obligatorio por fecha_inicio ascendente
        $sql = "
      SELECT 
        l.id_licencia,
        l.id_empleado,
        l.id_estado               AS id_estado,
        e.nombre                  AS estado,
        CONCAT(emp.apellido, ', ', emp.nombre) AS empleado,
        tl.descripcion            AS tipo,
        d.fecha_inicio,
        d.fecha_fin,
        d.cantidad_dias
      FROM licencia l
      JOIN empleados emp        ON emp.id_empleado   = l.id_empleado
      JOIN detalle_licencia d   ON d.id_licencia     = l.id_licencia
      JOIN tipos_licencia tl    ON tl.id_tipo        = l.id_tipo
      JOIN estados_licencia e   ON e.id_estado       = l.id_estado
      WHERE d.fecha_inicio BETWEEN :desde AND :hasta
         OR d.fecha_fin    BETWEEN :desde AND :hasta
         OR (:desde <= d.fecha_inicio AND :hasta >= d.fecha_fin) -- cubre períodos contenidos
      ORDER BY d.fecha_inicio ASC, l.id_licencia ASC
    ";
        $st = $this->PDO->prepare($sql);
        $st->execute([':desde' => $desde, ':hasta' => $hasta]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }



    public function editarLicenciaEmpleado(array $data): array
    {
        $idLicencia  = (int)($data['id_licencia'] ?? 0);
        $idEmpleado  = (int)($data['id_empleado'] ?? 0);
        $idTipoNuevo = (int)($data['id_tipo'] ?? 0);
        $fIniNueva   = trim($data['fecha_inicio'] ?? '');
        $fFinNueva   = trim($data['fecha_fin'] ?? '');

        if ($idLicencia <= 0 || $idEmpleado <= 0 || $idTipoNuevo <= 0 || $fIniNueva === '' || $fFinNueva === '') {
            return ['ok' => false, 'msg' => 'Datos incompletos'];
        }
        if (!$this->puedeEditar($idLicencia, $idEmpleado)) {
            return ['ok' => false, 'msg' => 'No autorizado para editar esta solicitud'];
        }

        // Recalcular días (servidor como autoridad)
        try {
            $dtI = new \DateTime($fIniNueva);
            $dtF = new \DateTime($fFinNueva);
            if ($dtF < $dtI) return ['ok' => false, 'msg' => 'Rango de fechas inválido'];
            $diasNuevo = (int)$dtI->diff($dtF)->days + 1;
        } catch (\Throwable $e) {
            return ['ok' => false, 'msg' => 'Fechas inválidas'];
        }

        // Valores actuales para auditar
        $sqlSel = "
      SELECT l.id_tipo, d.fecha_inicio, d.fecha_fin, d.cantidad_dias
        FROM licencia l
        JOIN detalle_licencia d ON d.id_licencia = l.id_licencia
       WHERE l.id_licencia = :id
       LIMIT 1";
        $stSel = $this->PDO->prepare($sqlSel);
        $stSel->execute([':id' => $idLicencia]);
        $curr = $stSel->fetch(\PDO::FETCH_ASSOC);
        if (!$curr) return ['ok' => false, 'msg' => 'No se encontró la licencia'];

        $cambios = [];
        if ((int)$curr['id_tipo'] !== $idTipoNuevo)
            $cambios[] = ['campo' => 'id_tipo', 'old' => $curr['id_tipo'], 'new' => $idTipoNuevo];
        if ((string)$curr['fecha_inicio'] !== $fIniNueva)
            $cambios[] = ['campo' => 'fecha_inicio', 'old' => $curr['fecha_inicio'], 'new' => $fIniNueva];
        if ((string)$curr['fecha_fin'] !== $fFinNueva)
            $cambios[] = ['campo' => 'fecha_fin', 'old' => $curr['fecha_fin'], 'new' => $fFinNueva];
        if ((int)$curr['cantidad_dias'] !== $diasNuevo)
            $cambios[] = ['campo' => 'cantidad_dias', 'old' => $curr['cantidad_dias'], 'new' => $diasNuevo];

        if (empty($cambios)) return ['ok' => true, 'msg' => 'No hubo cambios'];

        try {
            $this->PDO->beginTransaction();

            // maestro (refuerza propiedad del dueño)
            $upL = $this->PDO->prepare("UPDATE licencia SET id_tipo=:t WHERE id_licencia=:id AND id_empleado=:emp");
            $upL->execute([':t' => $idTipoNuevo, ':id' => $idLicencia, ':emp' => $idEmpleado]);


            // detalle (con días recalculados)
            $upD = $this->PDO->prepare("
            UPDATE detalle_licencia
               SET fecha_inicio=:fi, fecha_fin=:ff, cantidad_dias=:cd
             WHERE id_licencia=:id");
            $upD->execute([':fi' => $fIniNueva, ':ff' => $fFinNueva, ':cd' => $diasNuevo, ':id' => $idLicencia]);

            // auditoría (usás la misma tabla que ya tenías)
            $insA = $this->PDO->prepare("
          INSERT INTO licencia_cambios(id_licencia,id_empleado,campo,valor_anterior,valor_nuevo)
          VALUES(:id,:emp,:campo,:old,:new)");
            foreach ($cambios as $c) {
                $insA->execute([
                    ':id' => $idLicencia,
                    ':emp' => $idEmpleado,
                    ':campo' => $c['campo'],
                    ':old' => (string)$c['old'],
                    ':new' => (string)$c['new']
                ]);
            }

            $this->PDO->commit();
            return ['ok' => true];
        } catch (\Throwable $e) {
            $this->PDO->rollBack();
            return ['ok' => false, 'msg' => 'Error al guardar cambios'];
        }
    }

    public function reporteLicenciasEmpleadoPorPeriodo(int $idEmpleado, string $desde, string $hasta): array
    {
        $sql = "
      SELECT 
        l.id_licencia,
        l.id_empleado,
        l.id_estado               AS id_estado,
        e.nombre                  AS estado,
        tl.descripcion            AS tipo,
        d.fecha_inicio,
        d.fecha_fin,
        d.cantidad_dias
      FROM licencia l
      JOIN detalle_licencia d   ON d.id_licencia     = l.id_licencia
      JOIN tipos_licencia tl    ON tl.id_tipo        = l.id_tipo
      JOIN estados_licencia e   ON e.id_estado       = l.id_estado
      WHERE l.id_empleado = :emp
        AND (
              d.fecha_inicio BETWEEN :desde AND :hasta
           OR d.fecha_fin    BETWEEN :desde AND :hasta
           OR (:desde <= d.fecha_inicio AND :hasta >= d.fecha_fin)
        )
      ORDER BY d.fecha_inicio ASC, l.id_licencia ASC
    ";
        $st = $this->PDO->prepare($sql);
        $st->execute([':emp' => $idEmpleado, ':desde' => $desde, ':hasta' => $hasta]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function nombreEmpleadoPorId(int $idEmpleado): string
    {
        $sql = "SELECT TRIM(CONCAT(
                COALESCE(apellido,''),
                CASE WHEN COALESCE(apellido,'')<>'' AND COALESCE(nombre,'')<>'' THEN ', ' ELSE '' END,
                COALESCE(nombre,'')
            )) AS nom
            FROM empleados
            WHERE id_empleado = ?";
        $st = $this->PDO->prepare($sql);
        $st->execute([$idEmpleado]);
        return (string)($st->fetchColumn() ?: '');
    }

    /** Años de antigüedad al 31/12 del año dado */
    public function antiguedadAnios(int $idEmpleado, int $anio): int
    {
        $sql = "SELECT fecha_ingreso FROM empleados WHERE id_empleado = ?";
        $st  = $this->PDO->prepare($sql);
        $st->execute([$idEmpleado]);
        $fi = $st->fetchColumn();
        if (!$fi) return 0;
        // diferencia en años al 31/12 (cierre del año de vacaciones)
        $corte = sprintf('%d-12-31', $anio);
        $q = $this->PDO->query("SELECT TIMESTAMPDIFF(YEAR, '{$fi}', '{$corte}')");
        return (int)$q->fetchColumn();
    }

    /** Días anuales por antigüedad según vacaciones_config; si no hay tabla, aplica escala LCT hardcode */
    public function diasAnualesPorAntiguedad(int $idEmpleado, int $anio): int
    {
        // Intentar leer desde vacaciones_config
        $anios = $this->antiguedadAnios($idEmpleado, $anio);
        try {
            $sql = "
          SELECT dias
            FROM vacaciones_config
           WHERE antiguedad_desde <= :anios
             AND (antiguedad_hasta IS NULL OR :anios <= antiguedad_hasta)
           ORDER BY antiguedad_desde DESC
           LIMIT 1
        ";
            $st = $this->PDO->prepare($sql);
            $st->execute([':anios' => $anios]);
            $d = $st->fetchColumn();
            if ($d !== false) return (int)$d;
        } catch (\Throwable $e) {
            // si la tabla no existe, caemos al hardcode
        }
        // Fallback (LCT)
        if ($anios < 5) return 14;
        if ($anios < 10) return 21;
        if ($anios < 20) return 28;
        return 35;
    }

    /** Días aprobados en el año (estado=5 Aprobada), independiente del período de consulta */
    public function diasAprobadosAnio(int $idEmpleado, int $anio): int
    {
        $desde = "{$anio}-01-01";
        $hasta = "{$anio}-12-31";
        $sql = "
      SELECT COALESCE(SUM(d.cantidad_dias),0)
        FROM licencia l
        JOIN detalle_licencia d ON d.id_licencia = l.id_licencia
       WHERE l.id_empleado = :emp
         AND l.id_estado = 5
         AND (
              d.fecha_inicio BETWEEN :desde AND :hasta
           OR d.fecha_fin    BETWEEN :desde AND :hasta
           OR (:desde <= d.fecha_inicio AND :hasta >= d.fecha_fin)
         )
    ";
        $st = $this->PDO->prepare($sql);
        $st->execute([':emp' => $idEmpleado, ':desde' => $desde, ':hasta' => $hasta]);
        return (int)$st->fetchColumn();
    }

    public function countPendientesAprobacion(): int
    {
        $sql = "SELECT COUNT(*) FROM licencia WHERE id_estado = 3"; // Pendiente de aprobación
        return (int)$this->PDO->query($sql)->fetchColumn();
    }

    public function countAprobadasYTD(int $anio): int
    {
        $desde = "{$anio}-01-01";
        $hasta = "{$anio}-12-31";
        $sql = "SELECT COUNT(DISTINCT l.id_licencia)
            FROM licencia l
            JOIN detalle_licencia d ON d.id_licencia = l.id_licencia
            WHERE l.id_estado = 5
              AND (
                d.fecha_inicio BETWEEN :desde AND :hasta
                OR d.fecha_fin    BETWEEN :desde AND :hasta
                OR (:desde <= d.fecha_inicio AND :hasta >= d.fecha_fin)
              )";
        $st = $this->PDO->prepare($sql);
        $st->execute([':desde' => $desde, ':hasta' => $hasta]);
        return (int)$st->fetchColumn();
    }

    public function sumDiasVacacionesYTD(int $anio): int
    {
        $desde = "{$anio}-01-01";
        $hasta = "{$anio}-12-31";
        $sql = "SELECT COALESCE(SUM(d.cantidad_dias),0)
            FROM licencia l
            JOIN detalle_licencia d ON d.id_licencia = l.id_licencia
            JOIN tipos_licencia tl  ON tl.id_tipo    = l.id_tipo
            WHERE l.id_estado = 5
              AND tl.impacta_banco_vacaciones = 1
              AND (
                d.fecha_inicio BETWEEN :desde AND :hasta
                OR d.fecha_fin    BETWEEN :desde AND :hasta
                OR (:desde <= d.fecha_inicio AND :hasta >= d.fecha_fin)
              )";
        $st = $this->PDO->prepare($sql);
        $st->execute([':desde' => $desde, ':hasta' => $hasta]);
        return (int)$st->fetchColumn();
    }

    public function promDiasPorLicenciaAprobYTD(int $anio): float
    {
        $desde = "{$anio}-01-01";
        $hasta = "{$anio}-12-31";
        $sql = "WITH x AS (
              SELECT l.id_licencia, SUM(d.cantidad_dias) AS dias
              FROM licencia l
              JOIN detalle_licencia d ON d.id_licencia = l.id_licencia
              WHERE l.id_estado = 5
                AND (
                  d.fecha_inicio BETWEEN :desde AND :hasta
                  OR d.fecha_fin    BETWEEN :desde AND :hasta
                  OR (:desde <= d.fecha_inicio AND :hasta >= d.fecha_fin)
                )
              GROUP BY l.id_licencia
            )
            SELECT ROUND(AVG(dias),1) FROM x";
        $st = $this->PDO->prepare($sql);
        $st->execute([':desde' => $desde, ':hasta' => $hasta]);
        return (float)($st->fetchColumn() ?: 0);
    }

    public function countVacacionesAprobadasYTD(int $anio): int
    {
        $desde = "{$anio}-01-01";
        $hasta = "{$anio}-12-31";
        $sql = "SELECT COUNT(DISTINCT l.id_licencia)
            FROM licencia l
            JOIN detalle_licencia d ON d.id_licencia = l.id_licencia
            JOIN tipos_licencia tl  ON tl.id_tipo    = l.id_tipo
            WHERE l.id_estado = 5
              AND tl.impacta_banco_vacaciones = 1
              AND (
                d.fecha_inicio BETWEEN :desde AND :hasta
                OR d.fecha_fin    BETWEEN :desde AND :hasta
                OR (:desde <= d.fecha_inicio AND :hasta >= d.fecha_fin)
              )";
        $st = $this->PDO->prepare($sql);
        $st->execute([':desde' => $desde, ':hasta' => $hasta]);
        return (int)$st->fetchColumn();
    }

    public function countOtrasAprobadasYTD(int $anio): int
    {
        $desde = "{$anio}-01-01";
        $hasta = "{$anio}-12-31";
        $sql = "SELECT COUNT(DISTINCT l.id_licencia)
            FROM licencia l
            JOIN detalle_licencia d ON d.id_licencia = l.id_licencia
            LEFT JOIN tipos_licencia tl ON tl.id_tipo = l.id_tipo
            WHERE l.id_estado = 5
              AND (tl.impacta_banco_vacaciones IS NULL OR tl.impacta_banco_vacaciones = 0)
              AND (
                d.fecha_inicio BETWEEN :desde AND :hasta
                OR d.fecha_fin    BETWEEN :desde AND :hasta
                OR (:desde <= d.fecha_inicio AND :hasta >= d.fecha_fin)
              )";
        $st = $this->PDO->prepare($sql);
        $st->execute([':desde' => $desde, ':hasta' => $hasta]);
        return (int)$st->fetchColumn();
    }

    public function licenciasPorEstadoYTD(int $anio): array
    {
        $desde = "{$anio}-01-01";
        $hasta = "{$anio}-12-31";
        $sql = "SELECT el.nombre, COUNT(DISTINCT l.id_licencia) AS total
            FROM licencia l
            JOIN estados_licencia el ON el.id_estado = l.id_estado
            JOIN detalle_licencia d  ON d.id_licencia = l.id_licencia
            WHERE (
              d.fecha_inicio BETWEEN :desde AND :hasta
              OR d.fecha_fin    BETWEEN :desde AND :hasta
              OR (:desde <= d.fecha_inicio AND :hasta >= d.fecha_fin)
            )
            GROUP BY el.nombre
            ORDER BY total DESC";
        $st = $this->PDO->prepare($sql);
        $st->execute([':desde' => $desde, ':hasta' => $hasta]);
        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    public function licenciasPorTipoTopYTD(int $anio, int $lim = 5): array
    {
        $desde = "{$anio}-01-01";
        $hasta = "{$anio}-12-31";
        // OJO: nombre real es 'descripcion'
        $sql = "SELECT tl.descripcion AS nombre, COUNT(DISTINCT l.id_licencia) AS total
            FROM licencia l
            JOIN detalle_licencia d ON d.id_licencia = l.id_licencia
            JOIN tipos_licencia tl  ON tl.id_tipo    = l.id_tipo
            WHERE l.id_estado = 5
              AND (
                d.fecha_inicio BETWEEN :desde AND :hasta
                OR d.fecha_fin    BETWEEN :desde AND :hasta
                OR (:desde <= d.fecha_inicio AND :hasta >= d.fecha_fin)
              )
            GROUP BY tl.descripcion
            ORDER BY total DESC
            LIMIT {$lim}";
        $st = $this->PDO->prepare($sql);
        $st->execute([':desde' => $desde, ':hasta' => $hasta]);
        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    public function serieAprobadasPorMesYTD(int $anio): array
    {
        $desde = "{$anio}-01-01";
        $hasta = "{$anio}-12-31";
        $sql = "SELECT MONTH(d.fecha_inicio) AS mes, COUNT(DISTINCT l.id_licencia) AS total
            FROM licencia l
            JOIN detalle_licencia d ON d.id_licencia = l.id_licencia
            WHERE l.id_estado = 5
              AND d.fecha_inicio BETWEEN :desde AND :hasta
            GROUP BY MONTH(d.fecha_inicio)";
        $st = $this->PDO->prepare($sql);
        $st->execute([':desde' => $desde, ':hasta' => $hasta]);
        $rows = $st->fetchAll(\PDO::FETCH_KEY_PAIR) ?: [];
        $serie = [];
        for ($m = 1; $m <= 12; $m++) {
            $serie[] = (int)($rows[$m] ?? 0);
        }
        return $serie;
    }
}
