<?php
require_once("../modelo/modeloLicencias.php");

class ControladorLicencias
{
    private $m;

    public function __construct(?ModeloLicencias $m = null)
    {
        $this->m = $m ?: new ModeloLicencias();
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    }

    /* ========= Utilidades ========= */
    private function antiguedadEnAnios(?string $fechaIngreso, ?int $anioRef = null): int
    {
        if (!$fechaIngreso) return 0;
        $anioRef = $anioRef ?: (int)date('Y');
        $corte = sprintf('%d-12-31', $anioRef);
        $a = new DateTime($fechaIngreso);
        $b = new DateTime($corte);
        $diff = $a->diff($b);
        return max(0, (int)$diff->y);
    }

    private function diasVacacionesPorAntiguedad(int $anios): int
    {
        if ($anios >= 20) return 35;
        if ($anios >= 10) return 28;
        if ($anios >= 5)  return 21;
        return 14;
    }

    private function usuarioId(): ?int
    {
        return $_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
    }
    private function usuarioLogin(): ?string
    {
        return $_SESSION['user'] ?? null; // tu sesión suele guardar el login acá
    }



    /* ========= Endpoints (usables por POST desde la vista) ========= */

    /** Devuelve resumen + tipos + solicitudes para pintar la vista. */
    public function datosIniciales(): array
    {
        $emp = $this->empleadoFromSesion();
        if (!$emp || empty($emp['id_empleado'])) {
            return ['asociado' => false];
        }

        $anio = (int)date('Y');
        $ant  = $this->antiguedadEnAnios($emp['fecha_ingreso'] ?? null);
        $corr = $this->diasVacacionesPorAntiguedad($ant);

        // Lo que ya usabas (lo dejo igual para NO romper tu UI actual)
        $tom  = $this->m->diasTomadosAnio((int)$emp['id_empleado'], $anio);     // típicamente “vacaciones que impactan”
        $pen  = $this->m->diasPendientesAnio((int)$emp['id_empleado'], $anio);  // pendientes (tu criterio actual)
        $rest = max($corr - $tom - $pen, 0);

        // --- NUEVO: desglose claro de tomados ---
        // Si tenés los métodos en el modelo, los usamos; si no, caemos a valores compatibles (no rompe).
        $totTom = method_exists($this->m, 'diasAprobadosAnioTotal')
            ? (int)$this->m->diasAprobadosAnioTotal((int)$emp['id_empleado'], $anio)
            : (int)$tom;

        $vacTom = method_exists($this->m, 'diasAprobadosVacacionesAnio')
            ? (int)$this->m->diasAprobadosVacacionesAnio((int)$emp['id_empleado'], $anio)
            : (int)$tom;

        $otrTom = max(0, $totTom - $vacTom);

        // Si querés que el “restante” considere estrictamente vacaciones, podés recalcularlo con $vacTom:
        $restVac = max($corr - $vacTom - $pen, 0);

        return [
            'asociado'  => true,
            'empleado'  => $emp,
            'resumen'   => [
                'anio'                  => $anio,
                'antiguedad_anios'      => $ant,
                'dias_correspondientes' => $corr,
                'dias_tomados'          => $tom,      // se mantiene tal cual lo venías usando
                'dias_pendientes'       => $pen,
                'dias_restantes'        => $rest,     // se mantiene tal cual

                // --- NUEVO (no rompe nada): ---
                'total_tomados'         => $totTom,   // todas las aprobadas del año
                'tomados_vacaciones'    => $vacTom,   // subconjunto que impacta banco
                'tomados_otras'         => $otrTom,   // total - vacaciones
                'dias_restantes_vac'    => $restVac   // opcional: restante considerando solo vacaciones
            ],
            'tipos'       => $this->m->listarTipos(),
            'solicitudes' => $this->m->listarSolicitudes((int)$emp['id_empleado'])
        ];
    }



    /** Crea una licencia (borrador o enviada). */
    public function crear(): array
    {
        $emp = $this->empleadoFromSesion();
        if (!$emp || empty($emp['id_empleado'])) {
            return ['ok' => false, 'msg' => 'Usuario no asociado a un empleado.'];
        }

        $idEmpleado    = (int)$emp['id_empleado'];
        $idTipo        = (int)($_POST['id_tipo'] ?? 0);
        $fechaInicio   = trim($_POST['fecha_inicio'] ?? '');
        $cantidadDias  = (int)($_POST['cantidad_dias'] ?? 0);
        $obs           = trim($_POST['observaciones'] ?? '');
        $accion        = $_POST['submit_action'] ?? 'borrador'; // 'borrador'|'enviar'

        // Validaciones
        $err = [];

        if ($idTipo <= 0) $err[] = "Seleccioná un tipo de licencia.";
        if (!$fechaInicio || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio)) {
            $err[] = "Fecha de inicio inválida.";
        }
        if ($cantidadDias <= 0) $err[] = "La cantidad de días debe ser mayor o igual a 1.";
        if (mb_strlen($obs) > 200) $err[] = "Observaciones no puede superar 200 caracteres.";

        // Regla: fecha_inicio no puede ser más de 7 días en el pasado
        $minDate = date('Y-m-d', strtotime('-7 days'));
        if ($fechaInicio && $fechaInicio < $minDate) {
            $err[] = "La fecha de inicio no puede ser anterior a " . date('d/m/Y', strtotime($minDate)) . ".";
        }

        $fechaFin = $fechaInicio ? date('Y-m-d', strtotime($fechaInicio . ' + ' . max(0, $cantidadDias - 1) . ' day')) : null;

        // Solapamiento (contra Aprobadas y Pendientes de aprobación)
        if ($fechaInicio && $fechaFin) {
            if ($this->m->existeSolape($idEmpleado, $fechaInicio, $fechaFin)) {
                $err[] = "El rango seleccionado se solapa con otra licencia aprobada o pendiente de aprobación.";
            }
        }
                // --- Regla: no exceder días disponibles de vacaciones ---
        // Solo aplica cuando se ENVÍA la solicitud y el tipo descuenta del banco.
        if (
            $accion === 'enviar' &&
            $cantidadDias > 0 &&
            $fechaInicio &&
            $this->m->tipoImpactaBancoVacaciones($idTipo)
        ) {
            // Año de referencia: el año de la fecha de inicio
            $anio = (int)substr($fechaInicio, 0, 4);
            if ($anio < 2000 || $anio > 2100) {
                $anio = (int)date('Y');
            }

            // Antigüedad al cierre del año
            $ant = $this->antiguedadEnAnios($emp['fecha_ingreso'] ?? null, $anio);

            // Días que le corresponden por antigüedad
            $corr = $this->diasVacacionesPorAntiguedad($ant);

            // Días ya aprobados que impactan vacaciones
            if (method_exists($this->m, 'diasAprobadosVacacionesAnio')) {
                $vacTom = (int)$this->m->diasAprobadosVacacionesAnio($idEmpleado, $anio);
            } else {
                // fallback a tu método histórico
                $vacTom = (int)$this->m->diasTomadosAnio($idEmpleado, $anio);
            }

            // Días pendientes (solicitados y aún no definidos) que impactan vacaciones
            $pend = (int)$this->m->diasPendientesAnio($idEmpleado, $anio);

            // Días reales disponibles para vacaciones este año
            $restVac = max($corr - $vacTom - $pend, 0);

            if ($cantidadDias > $restVac) {
                $err[] = sprintf(
                    "No podés solicitar %d día(s) de vacaciones. Solo te quedan %d día(s) disponibles para el año %d.",
                    $cantidadDias,
                    $restVac,
                    $anio
                );
            }
        }


        if ($err) {
            return ['ok' => false, 'msg' => implode(' ', $err)];
        }

        // Estado según acción
        $estadoNombre = ($accion === 'enviar') ? 'Pendiente de aprobación' : 'Pendiente de envío';
        // Si querés que al crear siempre sea 'Nueva' y luego pase a 'Pendiente de envío' al guardar borrador, cambiamos aquí:
        // $estadoNombre = ($accion === 'enviar') ? 'Pendiente de aprobación' : 'Nueva';

        $idEstado = $this->m->estadoIdPorNombre($estadoNombre);
        if (!$idEstado) return ['ok' => false, 'msg' => 'Estado no configurado: ' . $estadoNombre];

        $idLic = $this->m->crearLicenciaConDetalle($idEmpleado, $idTipo, $idEstado, $obs, $fechaInicio, $cantidadDias, $accion === 'enviar');

        return ['ok' => true, 'id_licencia' => $idLic, 'msg' => 'Solicitud creada correctamente.'];
    }

    

    // Lista para panel de RRHH
    public function rrhh_listarPendientes(): array
    {
        if (!$this->esRRHH()) return ['ok' => false, 'msg' => 'No autorizado'];
        return ['ok' => true, 'items' => $this->m->listarPendientesRRHH()];
    }

    private function esRRHH(): bool
    {
        $rol = strtolower(trim($_SESSION['rol'] ?? ''));
        return in_array($rol, ['admin rrhh', 'administrador'], true);
    }

    function rrhh_listar(): array
    {
        if (!$this->esRRHH()) return ['ok' => false, 'msg' => 'No autorizado'];
        $items = $this->m->listarPendientesRRHH();
        return ['ok' => true, 'items' => $items];
    }

    public function rrhh_listar_tipos(): array
    {
        if (!$this->esRRHH()) {
            return ['ok' => false, 'msg' => 'No autorizado'];
        }

        // Usa el método del modelo que ya tenés
        $tipos = $this->m->listarTipos();

        return [
            'ok'    => true,
            'items' => $tipos
        ];
    }


    public function rrhh_resolver(): array
    {
        if (!$this->esRRHH()) return ['ok' => false, 'msg' => 'No autorizado'];

        $idLic  = (int)($_POST['id_licencia'] ?? 0);
        $accion = $_POST['accion_rrhh'] ?? '';  // 'aprobar' | 'rechazar'
        $obs    = trim((string)($_POST['observacion'] ?? ''));

        if ($idLic <= 0 || !in_array($accion, ['aprobar', 'rechazar'], true)) {
            return ['ok' => false, 'msg' => 'Datos inválidos'];
        }

        $nuevoEstadoId = ($accion === 'aprobar') ? 5 : 6;
        if ($nuevoEstadoId === 6 && $obs === '') {
            return ['ok' => false, 'msg' => 'Ingresá un motivo de rechazo.'];
        }

        $ok = $this->m->rrhhActualizarEstado($idLic, $nuevoEstadoId, $obs);
        return $ok ? ['ok' => true] : ['ok' => false, 'msg' => 'No se pudo actualizar'];
    }



    private function empleadoFromSesion(): ?array
    {
        // Usa tu propio método existente si ya lo tenés;
        // si no, reusamos el que ya venías usando en 'crear'
        if (!isset($_SESSION['id_usuario']) && !isset($_SESSION['user'])) return null;
        $usuarioId = $_SESSION['id_usuario'] ?? null;
        if (!$usuarioId && isset($_SESSION['user'])) {
            // si no tenés id en sesión, tu modelo tiene empleadoPorLogin() (ajusta si difiere)
            $emp = $this->m->empleadoPorLogin($_SESSION['user']);
            return $emp ?: null;
        }
        $emp = $this->m->empleadoPorUsuarioId((int)$usuarioId);
        return $emp ?: null;
    }

    /* ===== Detalle para el modal 'Ver' (empleado) ===== */
    public function detalle(): array
    {
        $idLic = (int)($_POST['id_licencia'] ?? 0);
        if ($idLic <= 0) return ['ok' => false, 'msg' => 'ID inválido'];

        $det = $this->m->detalleLicencia($idLic);
        if (!$det) return ['ok' => false, 'msg' => 'No encontrada'];

        // ¿el dueño?
        $emp = $this->empleadoFromSesion();
        $esDueno = $emp && !empty($emp['id_empleado']) && (int)$emp['id_empleado'] === (int)$det['id_empleado'];

        $estado = strtolower($det['estado'] ?? '');
        $acciones = [];
        if ($esDueno) {
            $idEstado = (int)($det['id_estado'] ?? 0);
            // IDs: 2 Pend. envío → Enviar + Cancelar
            //      3 Pend. aprobación → Cancelar
            if ($idEstado === 2) { // Pendiente de envío
                $acciones = ['enviar', 'cancelar'];
            } else if ($idEstado === 3) { // Pendiente de aprobación
                $acciones = ['cancelar'];
            }
        }

        return ['ok' => true, 'data' => $det, 'acciones' => $acciones];
    }

    /* ===== Cancelar (empleado) ===== */
    public function cancelar(): array
    {
        $emp = $this->empleadoFromSesion();
        if (!$emp || empty($emp['id_empleado'])) return ['ok' => false, 'msg' => 'Usuario no asociado a un empleado'];
        $idLic = (int)($_POST['id_licencia'] ?? 0);
        if ($idLic <= 0) return ['ok' => false, 'msg' => 'ID inválido'];

        $ok = $this->m->cancelarSiProcede($idLic, (int)$emp['id_empleado']);
        return $ok ? ['ok' => true] : ['ok' => false, 'msg' => 'No se puede cancelar en este estado'];
    }

    /* ===== Enviar existente (empleado) ===== */
    public function enviar(): array
    {
        $emp = $this->empleadoFromSesion();
        if (!$emp || empty($emp['id_empleado'])) return ['ok' => false, 'msg' => 'Usuario no asociado a un empleado'];
        $idLic = (int)($_POST['id_licencia'] ?? 0);
        if ($idLic <= 0) return ['ok' => false, 'msg' => 'ID inválido'];

        $ok = $this->m->enviarSiProcede($idLic, (int)$emp['id_empleado']);
        return $ok ? ['ok' => true] : ['ok' => false, 'msg' => 'No se puede enviar (estado inválido)'];
    }

    public function rrhh_detalle(): array
    {
        if (!$this->esRRHH()) return ['ok' => false, 'msg' => 'No autorizado'];

        $idLicencia = isset($_POST['id_licencia']) ? (int)$_POST['id_licencia'] : 0;
        if ($idLicencia <= 0) return ['ok' => false, 'msg' => 'Solicitud inválida'];

        $data = $this->m->detalleLicencia($idLicencia);
        if (!$data) return ['ok' => false, 'msg' => 'No se encontró el detalle'];

        // Acciones disponibles para RRHH según estado
        // (3 = Pendiente de aprobación → puede aprobar/rechazar)
        $acciones = [];
        if ((int)$data['id_estado'] === 3) {
            $acciones = ['aprobar', 'rechazar'];
        }

        return ['ok' => true, 'data' => $data, 'acciones' => $acciones];
    }


    public function editar(): array
    {
        // 1) Resolver usuario de la sesión (id o username)
        $usuarioId = $_SESSION['id_usuario']
            ?? $_SESSION['idUser']
            ?? $_SESSION['user_id']
            ?? null;

        $empleado = null;

        if ($usuarioId) {
            // 2a) Empleado vinculado por id de usuario
            $empleado = $this->m->empleadoPorUsuarioId((int)$usuarioId);
        } else {
            // 2b) Fallback: si solo hay username en sesión, resolver por login
            $usuarioNombre = $_SESSION['user'] ?? $_SESSION['usuario'] ?? null;
            if ($usuarioNombre) {
                $empleado = $this->m->empleadoPorLogin($usuarioNombre);
            }
        }

        if (!$empleado) {
            return ['ok' => false, 'msg' => 'Sesión inválida o sin empleado vinculado'];
        }
        $idEmpleado = (int)$empleado['id_empleado'];

        // 3) Datos del POST
        $idLicencia    = isset($_POST['id_licencia']) ? (int)$_POST['id_licencia'] : 0;
        $idTipo        = isset($_POST['id_tipo']) ? (int)$_POST['id_tipo'] : 0;
        $fechaInicio   = trim($_POST['fecha_inicio'] ?? '');
        $fechaFin      = trim($_POST['fecha_fin'] ?? '');
        $cantidadDias  = isset($_POST['cantidad_dias']) ? (int)$_POST['cantidad_dias'] : 0;

        if ($idLicencia <= 0 || $idTipo <= 0 || $fechaInicio === '' || $fechaFin === '' || $cantidadDias <= 0) {
            return ['ok' => false, 'msg' => 'Datos incompletos o inválidos'];
        }

        // 4) Llamar al modelo: ahí se valida PROPIEDAD + ESTADO por ID (2,3)
        $data = [
            'id_licencia'   => $idLicencia,
            'id_empleado'   => $idEmpleado,
            'id_tipo'       => $idTipo,
            'fecha_inicio'  => $fechaInicio,
            'fecha_fin'     => $fechaFin,
            'cantidad_dias' => $cantidadDias,
        ];

        return $this->m->editarLicenciaEmpleado($data);
    }

    public function rrhh_reporte_listar(): array
    {
        if (!$this->esRRHH()) return ['ok' => false, 'msg' => 'No autorizado'];

        $desde  = $_POST['desde']  ?? '';
        $hasta  = $_POST['hasta']  ?? '';
        $tipo   = $_POST['tipo']   ?? '';
        $estado = $_POST['estado'] ?? '';

        if ($desde === '' || $hasta === '' || $desde > $hasta) {
            return ['ok' => false, 'msg' => 'Rango de fechas inválido'];
        }

        $items = $this->m->reporteLicenciasPorPeriodo($desde, $hasta);

        // Filtro por tipo (si viene)
        if ($tipo !== '') {
            $items = array_values(array_filter($items, function ($row) use ($tipo) {
                if (isset($row['id_tipo']) && (string)$row['id_tipo'] === (string)$tipo) {
                    return true;
                }
                return false;
            }));
        }

        // Filtro por estado (si viene)
        if ($estado !== '') {
            $items = array_values(array_filter($items, function ($row) use ($estado) {
                if (isset($row['id_estado']) && (string)$row['id_estado'] === (string)$estado) {
                    return true;
                }
                return false;
            }));
        }

        return ['ok' => true, 'items' => $items];
    }



    public function rrhh_reporte_pdf(): void
    {
        // ===== HTML =====
        $titulo = 'Nómina de empleados';
        $fecha  = date('d/m/Y H:i');
        if (!$this->esRRHH()) {
            http_response_code(403);
            exit;
        }

        $desde = $_GET['desde'] ?? '';
        $hasta = $_GET['hasta'] ?? '';
        $tipo  = $_GET['tipo'] ?? '';
        $estado = $_GET['estado'] ?? '';

        if ($desde === '' || $hasta === '' || $desde > $hasta) {
            echo 'Rango de fechas inválido';
            exit;
        }

        $items = $this->m->reporteLicenciasPorPeriodo($desde, $hasta);

        // Mismo filtro por tipo que en el listado de pantalla
        if ($tipo !== '') {
            $items = array_values(array_filter($items, function ($row) use ($tipo) {
                if (isset($row['id_tipo_licencia']) && (string)$row['id_tipo_licencia'] === (string)$tipo) {
                    return true;
                }
                if (isset($row['id_tipo']) && (string)$row['id_tipo'] === (string)$tipo) {
                    return true;
                }
                if (isset($row['tipo']) && (string)$row['tipo'] === (string)$tipo) {
                    return true;
                }
                return false;
            }));
        }

        if ($estado !== '') {
            $items = array_values(array_filter($items, function ($row) use ($estado) {
                if (isset($row['id_estado']) && (string)$row['id_estado'] === (string)$estado) {
                    return true;
                }
                return false;
            }));
        }

        // Render HTML para PDF
        ob_start();
?>
        <html>

        <head>
            <style>
                body {
                    font-family: DejaVu Sans, Arial, sans-serif;
                    font-size: 12px;
                }

                h1 {
                    font-size: 18px;
                    margin: 0 0 10px 0;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                }

                th,
                td {
                    border: 1px solid #777;
                    padding: 6px;
                }

                th {
                    background: #efefef;
                }

                .center {
                    text-align: center;
                }

                .logo {
                    height: 50px;
                    max-width: 50px;
                }
            </style>
        </head>

        <body>
            <table width="100%" cellspacing="0" cellpadding="0" style="border-bottom: 2px solid #2c3e50; margin-bottom: 20px;">
                <tr>
                    <td>
                        <h1>Reporte integral de licencias</h1>
                        <p>Período: <?php echo date('d/m/Y', strtotime($desde)); ?> a <?php echo date('d/m/Y', strtotime($hasta)); ?></p>
                    </td>
                    <td style="text-align: right; vertical-align: middle;">
                        <img src="data:image/jpeg;base64,<?= base64_encode(file_get_contents('../../rsc/img/logo.jpg')) ?>" class="logo"
                            alt="Logo Empresa" />
                    </td>
                </tr>
            </table>
            <?php if (empty($items)): ?>
                <p><em>No se encontraron licencias en el período definido.</em></p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th class="center">Desde</th>
                            <th class="center">Hasta</th>
                            <th class="center">Días</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['empleado'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($r['tipo'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($r['estado'] ?? ''); ?></td>
                                <td class="center"><?php echo date('d/m/Y', strtotime($r['fecha_inicio'])); ?></td>
                                <td class="center"><?php echo date('d/m/Y', strtotime($r['fecha_fin'])); ?></td>
                                <td class="center"><?php echo (int)($r['cantidad_dias'] ?? 0); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </body>

        </html>
    <?php
        $html = ob_get_clean();

        // DOMPDF
        require_once __DIR__ . '/../../vendor/autoload.php'; // ajustá si tu autoload está en otra ruta
        $dompdf = new Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream('reporte_licencias_' . date('Ymd_His') . '.pdf', ['Attachment' => false]);
    }

    public function emp_reporte_listar(): array
    {
        // Resolver empleado según tu sesión
        $emp = $this->empleadoFromSesion();
        if (!$emp || empty($emp['id_empleado'])) {
            return ['ok' => false, 'msg' => 'Sesión inválida o sin empleado vinculado'];
        }
        $idEmpleado = (int)$emp['id_empleado'];

        $desde = $_POST['desde'] ?? '';
        $hasta = $_POST['hasta'] ?? '';
        if ($desde === '' || $hasta === '' || $desde > $hasta) {
            return ['ok' => false, 'msg' => 'Rango de fechas inválido'];
        }

        // Detalle del período
        $items = $this->m->reporteLicenciasEmpleadoPorPeriodo($idEmpleado, $desde, $hasta);

        // Resumen anual (año tomado del "hasta")
        $anio = (int)date('Y', strtotime($hasta));
        $anios = $this->antiguedadEnAnios($emp['fecha_ingreso'] ?? null, $anio);
        $diasAnuales = $this->diasVacacionesPorAntiguedad($anios);
        $tomadosYTD  = $this->m->diasAprobadosAnio($idEmpleado, $anio);
        $remanentes  = max(0, $diasAnuales - $tomadosYTD);

        return [
            'ok' => true,
            'items' => $items,
            'resumen' => [
                'anio'         => $anio,
                'dias_anuales' => $diasAnuales,
                'tomados_ytd'  => $tomadosYTD,
                'remanentes'   => $remanentes
            ]
        ];
    }

    public function stats_rrhh(): array
    {
        $anio = (int)date('Y');

        $kpis = [
            'pendientes_aprob' => $this->m->countPendientesAprobacion(),
            'aprobadas_ytd'    => $this->m->countAprobadasYTD($anio),
            'dias_vac_ytd'     => $this->m->sumDiasVacacionesYTD($anio),
            'prom_dias_aprob'  => $this->m->promDiasPorLicenciaAprobYTD($anio),
            'cant_vac_lic'     => $this->m->countVacacionesAprobadasYTD($anio),
            'cant_otras_lic'   => $this->m->countOtrasAprobadasYTD($anio),
        ];

        return [
            'ok'            => true,
            'kpis'          => $kpis,
            'por_estado'    => $this->m->licenciasPorEstadoYTD($anio),
            'por_tipo'      => $this->m->licenciasPorTipoTopYTD($anio, 5),
            'serie_mensual' => $this->m->serieAprobadasPorMesYTD($anio),
        ];
    }

    public function rrhh_listar_estados(): array
    {
        if (!$this->esRRHH()) {
            return ['ok' => false, 'msg' => 'No autorizado'];
        }

        $items = $this->m->listarEstados();

        return [
            'ok'    => true,
            'items' => $items
        ];
    }


    public function emp_reporte_pdf(): void
    {
        $emp = $this->empleadoFromSesion();
        if (!$emp || empty($emp['id_empleado'])) {
            http_response_code(403);
            exit;
        }

        $idEmpleado  = (int)$emp['id_empleado'];
        // Nombre robusto
        $nomEmpleado = '';
        if (!empty($emp['emp_apellido']) || !empty($emp['emp_nombre'])) {
            $nomEmpleado = trim($emp['emp_apellido'] ?? '');
            $nomEmpleado = $nomEmpleado !== '' ? $nomEmpleado . ', ' . trim($emp['emp_nombre'] ?? '') : trim($emp['emp_nombre'] ?? '');
        }
        if ($nomEmpleado === '') {
            $nomEmpleado = $this->m->nombreEmpleadoPorId($idEmpleado) ?: '(sin nombre)';
        }

        $desde = $_GET['desde'] ?? '';
        $hasta = $_GET['hasta'] ?? '';
        if ($desde === '' || $hasta === '' || $desde > $hasta) {
            echo 'Rango inválido';
            exit;
        }

        $items = $this->m->reporteLicenciasEmpleadoPorPeriodo($idEmpleado, $desde, $hasta);

        // Resumen anual
        $anio = (int)date('Y', strtotime($hasta));
        $anios = $this->antiguedadEnAnios($emp['fecha_ingreso'] ?? null, $anio);
        $diasAnuales = $this->diasVacacionesPorAntiguedad($anios);
        $tomadosYTD  = $this->m->diasAprobadosAnio($idEmpleado, $anio);
        $remanentes  = max(0, $diasAnuales - $tomadosYTD);

        ob_start(); ?>
        <html>

        <head>
            <meta charset="utf-8">
            <style>
                body {
                    font-family: DejaVu Sans, Arial, sans-serif;
                    font-size: 12px;
                }

                h1 {
                    font-size: 18px;
                    margin: 0 0 10px 0;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                }

                th,
                td {
                    border: 1px solid #777;
                    padding: 6px;
                }

                th {
                    background: #efefef;
                }

                .center {
                    text-align: center;
                }

                .logo {
                    height: 50px;
                    max-width: 50px;
                }
            </style>
        </head>

        <body>
            <h1 class="center">Mis licencias y vacaciones</h1>
            <br>
            <table>
                <td>
                    <p>Empleado: <strong><?php echo htmlspecialchars($nomEmpleado); ?></strong></p>
                    <p>Período: <?php echo date('d/m/Y', strtotime($desde)); ?> a <?php echo date('d/m/Y', strtotime($hasta)); ?></p>
                    <p>
                        Dias de licencia disponibles (<?php echo $anio; ?>): <strong><?php echo (int)$diasAnuales; ?></strong>
                        &nbsp;|&nbsp; Tomados Año en curso: <strong><?php echo (int)$tomadosYTD; ?></strong>
                        &nbsp;|&nbsp; Remanentes: <strong><?php echo (int)$remanentes; ?></strong>
                    </p>

                </td>
                <td style="text-align: center; vertical-align: middle;">
                    <img src="data:image/jpeg;base64,<?= base64_encode(file_get_contents('../../rsc/img/logo.jpg')) ?>" class="logo"
                        alt="Logo Empresa" />
                </td>

            </table>


            <?php if (empty($items)): ?>
                <p><em>No se encontraron licencias en el período definido.</em></p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th class="center">Desde</th>
                            <th class="center">Hasta</th>
                            <th class="center">Días</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['tipo'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($r['estado'] ?? ''); ?></td>
                                <td class="center"><?php echo date('d/m/Y', strtotime($r['fecha_inicio'])); ?></td>
                                <td class="center"><?php echo date('d/m/Y', strtotime($r['fecha_fin'])); ?></td>
                                <td class="center"><?php echo (int)($r['cantidad_dias'] ?? 0); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </body>

        </html>
<?php
        $html = ob_get_clean();

        require_once __DIR__ . '/../../vendor/autoload.php';
        $dompdf = new Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('mis_licencias_' . date('Ymd_His') . '.pdf', ['Attachment' => false]);
    }
}

function send_json($arr)
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($arr);
    exit;
}

/* Router SOLO si viene 'accion' (evita romper cuando se incluye desde una vista) */
if ((php_sapi_name() !== 'cli') && (isset($_POST['accion']) || isset($_GET['accion']))) {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $ctrl = new ControladorLicencias();

    $accion = $_POST['accion'] ?? $_GET['accion'];

    if ($accion === 'datos')                 send_json($ctrl->datosIniciales());
    else if ($accion === 'crear')            send_json($ctrl->crear());
    else if ($accion === 'detalle')          send_json($ctrl->detalle());
    else if ($accion === 'cancelar')         send_json($ctrl->cancelar());
    else if ($accion === 'enviar')           send_json($ctrl->enviar());
    else if ($accion === 'editar')           send_json($ctrl->editar());

    // RRHH (gestión de bandeja)
    else if ($accion === 'rrhh_listar')      send_json($ctrl->rrhh_listar());
    else if ($accion === 'rrhh_detalle')     send_json($ctrl->rrhh_detalle());
    else if ($accion === 'rrhh_listar_tipos')   send_json($ctrl->rrhh_listar_tipos());
    else if ($accion === 'rrhh_listar_estados') send_json($ctrl->rrhh_listar_estados());
    else if ($accion === 'rrhh_resolver')    send_json($ctrl->rrhh_resolver());
    // En tu router:
    else if ($accion === 'emp_reporte_listar') send_json($ctrl->emp_reporte_listar());
    else if ($accion === 'emp_reporte_pdf') {
        $ctrl->emp_reporte_pdf();
        exit;
    } else if ($accion === 'stats_rrhh') send_json($ctrl->stats_rrhh());
    // RRHH (reporte integral por período)
    else if ($accion === 'rrhh_reporte_listar') {
        send_json($ctrl->rrhh_reporte_listar());
    } else if ($accion === 'rrhh_reporte_pdf') {
        $ctrl->rrhh_reporte_pdf();
        exit;
    } else {
        send_json(['ok' => false, 'msg' => 'Acción no soportada'], 400);
    }
}
/* Fin router */
