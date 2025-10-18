<?php
class ModeloAdmEmpleado
{
    private $PDO;

    public function __construct()
    {
        require_once(__DIR__ . "/../../config/bd.php");
        $con = new bd();
        $this->PDO = $con->conexion();
    }

    public function listarTodos()
    {
        $sql = "SELECT
              e.id_empleado,
              e.nombre,
              e.apellido,
              e.sexo,
              e.fecha_nac,
              e.dni,
              e.cuil,
              e.legajo,
              e.id_puesto AS puesto_id,     -- 🔴 alias que usa la vista
              p.idPuesto  AS idPuesto,      -- (opcional, por si lo usás en otro lado)
              p.descrPuesto,
              e.fecha_ingreso,
              e.estado,
              e.email,
              e.telefono,
              e.direccion,
              e.usuario_id,
              e.provincia,
              e.id_estado_civil          AS estado_civil_id,     -- ✅ ID para el data-*
              ec.descripcion             AS estado_civil,        -- ✅ Texto para el data-*
              e.localidad              AS localidad_id,        -- ✅ ID que usaremos en data-localidad
              l.localidad              AS localidad_nombre,    -- ✅ Texto (por si querés mostrar o usar de fallback)
              CASE
                WHEN u.usuario IS NOT NULL AND u.usuario <> '' THEN u.usuario
                WHEN u.id IS NOT NULL THEN CONCAT('ID ', u.id)
                ELSE ''
              END AS usuario_label
            FROM empleados e
            LEFT JOIN usuarios u ON u.id = e.usuario_id
            LEFT JOIN puesto  p ON p.idPuesto = e.id_puesto
            LEFT JOIN estado_civil ec ON ec.id_estado_civil = e.id_estado_civil
            LEFT JOIN localidades   l  ON l.id_localidad = e.localidad
            WHERE e.eliminado = 0
            ORDER BY e.id_empleado ASC";
        $st = $this->PDO->query($sql);
        return $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
    }



    public function getProvincias(): array
    {
        $sql = "SELECT id_provincia, provincia FROM provincias ORDER BY provincia";
        $st = $this->PDO->query($sql);
        return $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getUsuariosParaCombo(): array
    {
        // Seguro: tu tabla usuarios garantiza 'id' y 'usuario'
        // (no asumimos columnas 'nombre'/'apellido' para evitar el 1054)
        $sql = "SELECT id, usuario FROM usuarios ORDER BY usuario";
        $st = $this->PDO->query($sql);
        return $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function traerPuesto()
    {
        $sql = "SELECT * FROM puesto";
        $st = $this->PDO->query($sql);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener($id)
    {
        $sql = "SELECT * FROM empleados WHERE id_empleado = :id";
        $st  = $this->PDO->prepare($sql);
        $st->bindParam(":id", $id, PDO::PARAM_INT);
        $st->execute();
        return $st->fetch(PDO::FETCH_ASSOC);
    }

    public function existeDni(string $dni, ?int $excluirId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM empleados WHERE dni = :dni";
        if ($excluirId) $sql .= " AND id_empleado <> :ex";
        $st = $this->PDO->prepare($sql);
        $st->bindValue(':dni', $dni);
        if ($excluirId) $st->bindValue(':ex', $excluirId, PDO::PARAM_INT);
        $st->execute();
        return (bool)$st->fetchColumn();
    }

    public function insertar($nombre, $apellido, $sexo, $fecha_nac, $dni, $cuil, $legajo, $email, $telefono, $direccion, $provincia, $localidad, $fecha_ingreso, $puesto, $estado, $usuario_id, $idEstadoCivil)
    {
        $sql = "INSERT INTO empleados
      (nombre,apellido,sexo,fecha_nac,dni,cuil,legajo,email,telefono,direccion,provincia,localidad,fecha_ingreso,id_puesto,estado,usuario_id,id_estado_civil)
      VALUES
      (:nombre,:apellido,:sexo,:fecha_nac,:dni,:cuil,:legajo,:email,:telefono,:direccion,:provincia,:localidad,:fecha_ingreso,:puesto,:estado,:usuario_id,:id_estado_civil)";
        $st = $this->PDO->prepare($sql);
        $st->bindValue(":nombre", $nombre);
        $st->bindValue(":apellido", $apellido);
        $st->bindValue(":sexo", $sexo ?: null);
        $st->bindValue(":fecha_nac", $fecha_nac ?: null);
        $st->bindValue(":dni", $dni);
        $st->bindValue(":cuil", $cuil ?: null);
        $st->bindValue(":legajo", $legajo ?: null);
        $st->bindValue(":email", $email ?: null);
        $st->bindValue(":telefono", $telefono ?: null);
        $st->bindValue(":direccion", $direccion ?: null);
        $st->bindValue(":provincia", (int)$provincia, PDO::PARAM_INT);
        $st->bindValue(":localidad", (int)$localidad, PDO::PARAM_INT);
        $st->bindValue(":fecha_ingreso", $fecha_ingreso);
        $st->bindValue(":puesto", (int)$puesto, PDO::PARAM_INT);
        $st->bindValue(":estado", $estado);
        $st->bindValue(":usuario_id", $usuario_id ?: null, $usuario_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $st->bindValue(':id_estado_civil', (int)$idEstadoCivil, PDO::PARAM_INT);
        return $st->execute() ? (int)$this->PDO->lastInsertId() : false;
    }

    public function update($id, $nombre, $apellido, $sexo, $fecha_nac, $dni, $cuil, $legajo, $email, $telefono, $direccion, $provincia, $localidad, $fecha_ingreso, $puesto, $estado, $usuario_id, $idEstadoCivil)
    {
        $sql = "UPDATE empleados
               SET nombre=:nombre, apellido=:apellido, sexo=:sexo, fecha_nac=:fecha_nac,
                   dni=:dni, cuil=:cuil, legajo=:legajo, email=:email, telefono=:telefono, direccion=:direccion,
                   provincia=:provincia, localidad=:localidad, fecha_ingreso=:fecha_ingreso, id_puesto=:puesto, estado=:estado,
                   usuario_id=:usuario_id, id_estado_civil=:id_estado_civil
             WHERE id_empleado=:id";
        $st = $this->PDO->prepare($sql);
        $st->bindValue(":nombre", $nombre);
        $st->bindValue(":apellido", $apellido);
        $st->bindValue(":sexo", $sexo ?: null);
        $st->bindValue(":fecha_nac", $fecha_nac ?: null);
        $st->bindValue(":dni", $dni);
        $st->bindValue(":cuil", $cuil ?: null);
        $st->bindValue(":legajo", $legajo ?: null);
        $st->bindValue(":email", $email ?: null);
        $st->bindValue(":telefono", $telefono ?: null);
        $st->bindValue(":direccion", $direccion ?: null);
        $st->bindValue(":provincia", (int)$provincia, PDO::PARAM_INT);
        $st->bindValue(":localidad", (int)$localidad, PDO::PARAM_INT);
        $st->bindValue(":fecha_ingreso", $fecha_ingreso);
        $st->bindValue(":puesto", $puesto);
        $st->bindValue(":estado", $estado);
        $st->bindValue(":usuario_id", $usuario_id ?: null, $usuario_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $st->bindValue(":id", (int)$id, PDO::PARAM_INT);
        $st->bindValue(':id_estado_civil', (int)$idEstadoCivil, PDO::PARAM_INT);
        return $st->execute() ? $id : false;
    }

    /** Baja lógica + inactiva usuario vinculado si lo hay */
    public function inactivar(int $id): bool
    {
        try {
            $this->PDO->beginTransaction();

            // Traer usuario vinculado
            $emp = $this->obtener($id);
            if (!$emp) {
                $this->PDO->rollBack();
                return false;
            }

            // Empleado → Inactivo
            $st = $this->PDO->prepare("UPDATE empleados SET estado='Inactivo' WHERE id_empleado=:id");
            $st->bindValue(':id', $id, PDO::PARAM_INT);
            if (!$st->execute()) {
                $this->PDO->rollBack();
                return false;
            }

            // Si hay usuario_id, inactivarlo (NO lo eliminamos)
            if (!empty($emp['usuario_id'])) {
                $st2 = $this->PDO->prepare("UPDATE usuarios SET estado='Inactivo' WHERE id=:uid");
                $st2->bindValue(':uid', (int)$emp['usuario_id'], PDO::PARAM_INT);
                if (!$st2->execute()) {
                    $this->PDO->rollBack();
                    return false;
                }
            }

            return $this->PDO->commit();
        } catch (Throwable $e) {
            if ($this->PDO->inTransaction()) $this->PDO->rollBack();
            return false;
        }
    }

    /** (Opcional) activar empleado. No tocamos usuario (según tu política) */
    public function activar(int $id): bool
    {
        $st = $this->PDO->prepare("UPDATE empleados SET estado='Activo' WHERE id_empleado=:id");
        $st->bindValue(':id', $id, PDO::PARAM_INT);
        return $st->execute();
    }

    /** Cambia Activo↔Inactivo. Si pasa a Inactivo, también inactiva el usuario. */
    public function toggleEstado(int $id): bool
    {
        $emp = $this->obtener($id);
        if (!$emp) return false;
        if ($emp['estado'] === 'Activo') {
            return $this->inactivar($id);
        } else {
            return $this->activar($id); // no activamos usuario asociado
        }
    }

    /** DEPRECADO: mantener por compatibilidad si alguna vista lo llama aún */
    public function eliminar($id)
    {
        // Redirigir a baja lógica
        return $this->inactivar((int)$id);
    }

    public function getLocalidadesMap(): array
    {
        $sql = "SELECT id_localidad, localidad FROM localidades";
        $st = $this->PDO->query($sql);
        $map = [];
        if ($st) {
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $map[(int)$r['id_localidad']] = $r['localidad'];
            }
        }
        return $map;
    }

    public function existeCuil(string $cuil, ?int $excluirId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM empleados WHERE cuil = :cuil";
        if ($excluirId) $sql .= " AND id_empleado <> :ex";
        $st = $this->PDO->prepare($sql);
        $st->bindValue(':cuil', $cuil);
        if ($excluirId) $st->bindValue(':ex', $excluirId, PDO::PARAM_INT);
        $st->execute();
        return (bool)$st->fetchColumn();
    }

    public function existeLegajo(string $legajo, ?int $excluirId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM empleados WHERE legajo = :legajo";
        if ($excluirId) $sql .= " AND id_empleado <> :ex";
        $st = $this->PDO->prepare($sql);
        $st->bindValue(':legajo', $legajo);
        if ($excluirId) $st->bindValue(':ex', $excluirId, PDO::PARAM_INT);
        $st->execute();
        return (bool)$st->fetchColumn();
    }

    // ================== BUSCADOR DataTables (con FK a puesto) ==================
    public function buscarEmpleadosDT(
        string $q,
        int $start,
        int $length,
        string $orderColKey,
        string $orderDir,
        ?string $fEstado = null,   // 'Activo' | 'Inactivo' | null
        ?int $fPuesto = null,      // id_puesto o null
        ?string $fDesde = null,    // 'YYYY-MM-DD' o ''
        ?string $fHasta = null     // 'YYYY-MM-DD' o ''
    ): array {
        // Detectar PDO según tu clase
        $pdo = $this->PDO ?? ($this->db ?? null);
        if (!$pdo instanceof PDO) {
            throw new RuntimeException('No se encontró conexión PDO en el modelo.');
        }

        // Whitelist de columnas (DataTables → SQL seguro)
        $columnsMap = [
            'legajo'     => 'e.legajo',
            'nombre'     => 'e.nombre',
            'apellido'   => 'e.apellido',
            'dni'        => 'e.dni',
            'email'      => 'e.email',
            'puesto'     => 'p.descrPuesto',     // mostrar/ordenar por descripción del puesto
            'estado'     => 'e.estado',
            'fecha_alta' => 'e.fecha_ingreso',   // exponemos como fecha_alta
        ];
        $orderCol = $columnsMap[$orderColKey] ?? 'e.apellido';
        $orderDir = (strtolower($orderDir) === 'desc') ? 'DESC' : 'ASC';

        // FROM + JOIN
        $from = " FROM empleados e
              LEFT JOIN puesto p ON p.idPuesto = e.id_puesto ";

        // WHERE base: ocultar eliminados
        $where = ["e.eliminado = 0"];
        $params = [];

        // Búsqueda global (incluye puesto)
        if ($q !== '') {
            $where[] = "(e.legajo LIKE :q OR e.nombre LIKE :q OR e.apellido LIKE :q OR e.dni LIKE :q OR e.email LIKE :q OR p.descrPuesto LIKE :q)";
            $params[':q'] = "%{$q}%";
        }

        // Filtros
        if (!empty($fEstado)) {
            $where[] = "e.estado = :fEstado";           // 'Activo'/'Inactivo'
            $params[':fEstado'] = $fEstado;
        }
        if (!empty($fPuesto)) {
            $where[] = "e.id_puesto = :fPuesto";        // id numérico
            $params[':fPuesto'] = (int)$fPuesto;
        }
        if (!empty($fDesde)) {
            $where[] = "e.fecha_ingreso >= :fDesde";
            $params[':fDesde'] = $fDesde;
        }
        if (!empty($fHasta)) {
            $where[] = "e.fecha_ingreso <= :fHasta";
            $params[':fHasta'] = $fHasta;
        }

        $whereSql = ' WHERE ' . implode(' AND ', $where);

        // Totales
        $sqlTotal = "SELECT COUNT(*) AS c FROM empleados e WHERE e.eliminado = 0";
        $total = (int)$pdo->query($sqlTotal)->fetch(PDO::FETCH_ASSOC)['c'];

        $sqlFiltrado = "SELECT COUNT(*) AS c {$from} {$whereSql}";
        $st = $pdo->prepare($sqlFiltrado);
        foreach ($params as $k => $v) $st->bindValue($k, $v);
        $st->execute();
        $filtrado = (int)$st->fetch(PDO::FETCH_ASSOC)['c'];

        // Datos
        $sqlDatos = "SELECT
                    e.id_empleado AS id,
                    e.legajo,
                    e.nombre,
                    e.apellido,
                    e.dni,
                    e.email,
                    COALESCE(p.descrPuesto, 'Sin asignar') AS puesto,
                    e.estado,
                    e.fecha_ingreso AS fecha_alta
                 {$from}
                 {$whereSql}
                 ORDER BY {$orderCol} {$orderDir}
                 LIMIT :start, :length";
        $st = $pdo->prepare($sqlDatos);
        foreach ($params as $k => $v) $st->bindValue($k, $v);
        $st->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $st->bindValue(':length', (int)$length, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        return [
            'total'    => $total,
            'filtrado' => $filtrado,
            'rows'     => $rows
        ];
    }

    // ================== DETALLE: obtener un empleado por ID ==================
    public function obtenerEmpleadoPorId(int $idEmpleado): ?array
    {
        $sql = "SELECT
                e.id_empleado          AS id,
                e.legajo,
                e.nombre,
                e.apellido,
                e.dni,
                e.sexo,                          -- ⚠ si tu columna se llama distinto, ajustar
                e.fecha_nac    AS fecha_nacimiento, -- ⚠ ajustar si es fechanac
                e.cuil,                          -- ⚠ ajustar si tu columna se llama distinto
                e.email,
                e.telefono,                      -- ⚠ ajustar si es telefono_particular
                e.direccion,
                prov.provincia                                AS provincia,
                loc.localidad                                 AS localidad,
                COALESCE(p.descrPuesto,'Sin asignar') AS puesto,
                e.estado,
                e.id_puesto         AS id_puesto,
                e.fecha_ingreso        AS fecha_alta,
                u.usuario              AS usuario,  -- ⚠ si no existe tabla usuarios, poner NULL AS usuario
                e.id_estado_civil AS id_estado_civil,
                ec.descripcion     AS estado_civil
            FROM empleados e
            LEFT JOIN puesto p   ON p.idPuesto = e.id_puesto
            LEFT JOIN provincias  prov ON prov.id_provincia= e.provincia
            LEFT JOIN localidades loc  ON loc.id_localidad = e.localidad
            LEFT JOIN usuarios u ON u.id = e.usuario_id  -- ⚠ si no existe, eliminar este JOIN
            LEFT JOIN estado_civil ec ON ec.id_estado_civil = e.id_estado_civil
            
            WHERE e.id_empleado = :id AND e.eliminado = 0
            LIMIT 1";
        $st = $this->PDO->prepare($sql);
        $st->bindValue(':id', $idEmpleado, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getEstadisticasEmpleados(): array
    {
        // KPIs
        $kpiActivos = (int)$this->PDO->query("SELECT COUNT(*) c FROM empleados WHERE eliminado=0 AND estado='Activo'")->fetchColumn();
        $kpiInact   = (int)$this->PDO->query("SELECT COUNT(*) c FROM empleados WHERE eliminado=0 AND estado='Inactivo'")->fetchColumn();
        $kpiAltas30 = (int)$this->PDO->query("
        SELECT COUNT(*) c
        FROM empleados
        WHERE eliminado=0 AND fecha_ingreso >= (CURDATE() - INTERVAL 30 DAY)
    ")->fetchColumn();
        $kpiAntig   = (float)$this->PDO->query("
        SELECT COALESCE(AVG(TIMESTAMPDIFF(MONTH, fecha_ingreso, CURDATE()))/12,0) prom
        FROM empleados
        WHERE eliminado=0
    ")->fetchColumn();

        // Altas por mes (últimos 12)
        $st = $this->PDO->query("
        SELECT DATE_FORMAT(fecha_ingreso,'%Y-%m') mes, COUNT(*) altas
        FROM empleados
        WHERE eliminado=0
          AND fecha_ingreso >= DATE_FORMAT(CURDATE() - INTERVAL 11 MONTH,'%Y-%m-01')
        GROUP BY mes
        ORDER BY mes
    ");
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        // completar meses faltantes
        $labelsMes = [];
        $dataMes = [];
        $start = new DateTime(date('Y-m-01', strtotime('-11 months')));
        for ($i = 0; $i < 12; $i++) {
            $k = $start->format('Y-m');
            $labelsMes[] = $k;
            $found = 0;
            foreach ($rows as $r) if ($r['mes'] === $k) {
                $found = (int)$r['altas'];
                break;
            }
            $dataMes[] = $found;
            $start->modify('+1 month');
        }

        // Por puesto (top 10)
        $st = $this->PDO->query("
        SELECT COALESCE(p.descrPuesto,'Sin asignar') puesto, COUNT(*) cant
        FROM empleados e
        LEFT JOIN puesto p ON p.idPuesto = e.id_puesto
        WHERE e.eliminado=0
        GROUP BY puesto
        ORDER BY cant DESC
        LIMIT 10
    ");
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        $labPuesto = array_column($rows, 'puesto');
        $datPuesto = array_map('intval', array_column($rows, 'cant'));

        // Por estado
        $st = $this->PDO->query("
        SELECT estado, COUNT(*) cant
        FROM empleados
        WHERE eliminado=0
        GROUP BY estado
    ");
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        $labEstado = array_column($rows, 'estado');
        $datEstado = array_map('intval', array_column($rows, 'cant'));

        // Por género (si no existe columna, reemplazar por NULL y quedará vacío)
        $st = $this->PDO->query("
        SELECT COALESCE(sexo,'No informado') sexo, COUNT(*) cant
        FROM empleados
        WHERE eliminado=0
        GROUP BY sexo
    ");
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        $labGenero = array_column($rows, 'sexo');
        $datGenero = array_map('intval', array_column($rows, 'cant'));

        // Antigüedad (buckets)
        $st = $this->PDO->query("
        SELECT bucket, COUNT(*) cant FROM (
          SELECT CASE
            WHEN TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()) < 1  THEN '0–1'
            WHEN TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()) < 3  THEN '1–3'
            WHEN TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()) < 5  THEN '3–5'
            WHEN TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()) < 10 THEN '5–10'
            ELSE '10+'
          END AS bucket
          FROM empleados
          WHERE eliminado=0
        ) t
        GROUP BY bucket
        ORDER BY FIELD(bucket,'0–1','1–3','3–5','5–10','10+')
    ");
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        $labAnt   = array_column($rows, 'bucket');
        $datAnt   = array_map('intval', array_column($rows, 'cant'));

        // Por provincia (top 10)
        $st = $this->PDO->query("
        SELECT COALESCE(prov.provincia,'Sin dato') provincia, COUNT(*) cant
        FROM empleados e
        LEFT JOIN provincias prov ON prov.id_provincia = e.provincia
        WHERE e.eliminado=0
        GROUP BY provincia
        ORDER BY cant DESC
        LIMIT 10
    ");
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        $labProv = array_column($rows, 'provincia');
        $datProv = array_map('intval', array_column($rows, 'cant'));

        return [
            'kpis' => [
                'activos'         => $kpiActivos,
                'inactivos'       => $kpiInact,
                'altas_30d'       => $kpiAltas30,
                'antiguedad_prom' => $kpiAntig,
            ],
            'altas_por_mes' => [
                'labels' => $labelsMes,
                'data'   => $dataMes,
            ],
            'por_puesto' => [
                'labels' => $labPuesto,
                'data'   => $datPuesto,
            ],
            'por_estado' => [
                'labels' => $labEstado,
                'data'   => $datEstado,
            ],
            'por_genero' => [
                'labels' => $labGenero,
                'data'   => $datGenero,
            ],
            'antiguedad' => [
                'labels' => $labAnt,
                'data'   => $datAnt,
            ],
            'por_provincia' => [
                'labels' => $labProv,
                'data'   => $datProv,
            ],
        ];
    }

    public function listarNomina(
    ?string $q = null,
    ?string $estado = null,
    ?int $idPuesto = null,
    ?string $desde = null,
    ?string $hasta = null,
    string $orderCol = 'apellido',
    string $orderDir = 'asc'
): array {
    // Whitelists para ORDER BY (evita 1064 por columnas inválidas)
    $cols = [
        'apellido'       => 'e.apellido',
        'nombre'         => 'e.nombre',
        'dni'            => 'e.dni',
        'legajo'         => 'e.legajo',
        'fecha_ingreso'  => 'e.fecha_ingreso',
        'estado'         => 'e.estado',
        'puesto'         => 'p.descrPuesto',
        'provincia'      => 'pr.provincia',
        'localidad'      => 'l.localidad'
    ];
    $dirs = ['asc' => 'ASC', 'desc' => 'DESC'];

    $orderBy = $cols[$orderCol] ?? 'e.apellido';
    $direction = $dirs[strtolower($orderDir)] ?? 'ASC';

    $sql = "
        SELECT
            e.id_empleado,
            e.apellido,
            e.nombre,
            e.dni,
            e.legajo,
            e.email,
            e.telefono,
            e.direccion,
            e.fecha_ingreso,
            e.estado,
            p.descrPuesto         AS puesto,
            pr.provincia          AS provincia,
            l.localidad           AS localidad,
            COALESCE(ec.descripcion, 'No informado') AS estado_civil
        FROM empleados e
        LEFT JOIN puesto        p  ON p.idPuesto = e.id_puesto
        LEFT JOIN provincias    pr ON pr.id_provincia = e.provincia
        LEFT JOIN localidades   l  ON l.id_localidad = e.localidad
        LEFT JOIN estado_civil  ec ON ec.id_estado_civil = e.id_estado_civil
        WHERE e.eliminado = 0
    ";

    $params = [];

    if ($q !== null && $q !== '') {
        $sql .= " AND (
            e.apellido LIKE :q
            OR e.nombre LIKE :q
            OR e.dni LIKE :q
            OR e.legajo LIKE :q
            OR e.email LIKE :q
            OR e.telefono LIKE :q
            OR p.descrPuesto LIKE :q
            OR pr.provincia LIKE :q
            OR l.localidad LIKE :q
        )";
        $params[':q'] = '%' . $q . '%';
    }

    if ($estado !== null && $estado !== '') {
        $sql .= " AND e.estado = :estado";
        $params[':estado'] = $estado;
    }
    if (!is_null($idPuesto) && $idPuesto > 0) {
        $sql .= " AND e.id_puesto = :id_puesto";
        $params[':id_puesto'] = $idPuesto;
    }
    if ($desde) {
        $sql .= " AND e.fecha_ingreso >= :desde";
        $params[':desde'] = $desde;
    }
    if ($hasta) {
        $sql .= " AND e.fecha_ingreso <= :hasta";
        $params[':hasta'] = $hasta;
    }

    $sql .= " ORDER BY {$orderBy} {$direction}";

    $st = $this->PDO->prepare($sql);
    foreach ($params as $k => $v) {
        $st->bindValue($k, $v);
    }
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC);
}


    public function getEstadosCiviles(): array
    {
        $sql = "SELECT id_estado_civil AS id, descripcion FROM estado_civil ORDER BY descripcion";
        $st = $this->PDO->query($sql);
        return $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
    }
}
