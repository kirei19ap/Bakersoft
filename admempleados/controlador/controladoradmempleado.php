<?php
require_once(__DIR__ . "/../modelo/modeloadmempleado.php");

class ControladorAdmEmpleado
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new ModeloAdmEmpleado();
    }

    public function listar()
    {
        return $this->modelo->listarTodos();
    }
    public function obtener($id)
    {
        return $this->modelo->obtener($id);
    }

    private function normalizarFecha($f)
    {
        if (!$f) return null;
        // Si viene en Y-m-d, devuelvo tal cual; si viene dd/mm/aaaa, la convierto
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $f)) return $f;
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $f, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        return $f;
    }
    private function normalizarCuil($v)
    {
        if (!$v) return null;
        $digits = preg_replace('/\D+/', '', $v);
        return $digits ?: null; // guardamos 11 dígitos
    }

    private function cuilValido($digits11): bool
    {
        return (bool)preg_match('/^\d{11}$/', $digits11);
    }

    private function fechaNoFutura($fYmd): bool
    {
        if (!$fYmd) return false;
        return (strtotime($fYmd) <= strtotime(date('Y-m-d')));
    }

    public function guardar($p)
    {
        $p = array_map(fn($v) => is_string($v) ? trim($v) : $v, $p);
        $p['fecha_ingreso'] = $this->normalizarFecha($p['fecha_ingreso'] ?? null);
        $p['fecha_nac']     = $this->normalizarFecha($p['fecha_nac'] ?? null);
        $p['cuil']          = $this->normalizarCuil($p['cuil'] ?? null);

        // DNI único (ya lo teníamos)
        if ($this->modelo->existeDni($p['dni'])) {
            $_SESSION['flash_error'] = "Ya existe un empleado con el DNI {$p['dni']}.";
            return false;
        }
        // fecha_ingreso no futura
        if (!$this->fechaNoFutura($p['fecha_ingreso'])) {
            $_SESSION['flash_error'] = "La fecha de ingreso no puede ser futura.";
            return false;
        }
        // fecha_nac no futura (si viene)
        if (!empty($p['fecha_nac']) && !$this->fechaNoFutura($p['fecha_nac'])) {
            $_SESSION['flash_error'] = "La fecha de nacimiento no puede ser futura.";
            return false;
        }
        // CUIL: si viene, validar y unicidad
        if (!empty($p['cuil'])) {
            if (!$this->cuilValido($p['cuil'])) {
                $_SESSION['flash_error'] = "El CUIL debe tener 11 dígitos (sin guiones).";
                return false;
            }
            if ($this->modelo->existeCuil($p['cuil'])) {
                $_SESSION['flash_error'] = "Ya existe un empleado con el CUIL ingresado.";
                return false;
            }
        }
        // Legajo: si viene, unicidad
        if (!empty($p['legajo']) && $this->modelo->existeLegajo($p['legajo'])) {
            $_SESSION['flash_error'] = "Ya existe un empleado con ese Legajo.";
            return false;
        }

        $p['estado'] = $p['estado'] ?? 'Activo';

        return $this->modelo->insertar(
            $p['nombre'],
            $p['apellido'],
            $p['sexo'] ?? null,
            $p['fecha_nac'] ?? null,
            $p['dni'],
            $p['cuil'] ?? null,
            $p['legajo'] ?? null,
            $p['email'] ?? null,
            $p['telefono'] ?? null,
            $p['direccion'] ?? null,
            $p['provincia'],
            $p['localidad'],
            $p['fecha_ingreso'],
            $p['puesto'],
            $p['estado'],
            $p['usuario_id'] ?? null,
            $p['id_estado_civil'] ?? null
        );
    }

    public function actualizar($p)
    {
        $p = array_map(fn($v) => is_string($v) ? trim($v) : $v, $p);
        $p['fecha_ingreso'] = $this->normalizarFecha($p['fecha_ingreso'] ?? null);
        $p['fecha_nac']     = $this->normalizarFecha($p['fecha_nac'] ?? null);
        $p['cuil']          = $this->normalizarCuil($p['cuil'] ?? null);

        $id = (int)($p['id_empleado'] ?? 0);
        if ($id <= 0) {
            $_SESSION['flash_error'] = "ID inválido.";
            return false;
        }

        $empActual = $this->modelo->obtener($id);
        if (!$empActual) {
            $_SESSION['flash_error'] = "Empleado inexistente.";
            return false;
        }

        // DNI inmutable
        if (isset($p['dni']) && $p['dni'] !== $empActual['dni']) {
            $_SESSION['flash_error'] = "El DNI no puede modificarse.";
            return false;
        }

        if (!$this->fechaNoFutura($p['fecha_ingreso'])) {
            $_SESSION['flash_error'] = "La fecha de ingreso no puede ser futura.";
            return false;
        }
        if (!empty($p['fecha_nac']) && !$this->fechaNoFutura($p['fecha_nac'])) {
            $_SESSION['flash_error'] = "La fecha de nacimiento no puede ser futura.";
            return false;
        }
        if (!empty($p['cuil'])) {
            if (!$this->cuilValido($p['cuil'])) {
                $_SESSION['flash_error'] = "El CUIL debe tener 11 dígitos (sin guiones).";
                return false;
            }
            if ($this->modelo->existeCuil($p['cuil'], $id)) {
                $_SESSION['flash_error'] = "Ya existe un empleado con el CUIL ingresado.";
                return false;
            }
        }
        if (!empty($p['legajo']) && $this->modelo->existeLegajo($p['legajo'], $id)) {
            $_SESSION['flash_error'] = "Ya existe un empleado con ese Legajo.";
            return false;
        }

        return $this->modelo->update(
            $id,
            $p['nombre'],
            $p['apellido'],
            $p['sexo'] ?? null,
            $p['fecha_nac'] ?? null,
            $empActual['dni'],
            $p['cuil'] ?? null,
            $p['legajo'] ?? null,
            $p['email'] ?? null,
            $p['telefono'] ?? null,
            $p['direccion'] ?? null,
            $p['provincia'],
            $p['localidad'],
            $p['fecha_ingreso'],
            $p['puesto'],
            $p['estado'] ?? 'Activo',
            $p['usuario_id'] ?? null,
            $p['id_estado_civil'] ?? null
        );
    }

    public function toggle($id)
    {
        return $this->modelo->toggleEstado((int)$id);
    }

    public function borrar($id)
    {
        // Mantener compat, pero redirige a baja lógica
        return $this->modelo->inactivar((int)$id);
    }

    public function provincias()
    {
        return $this->modelo->getProvincias();
    }

    public function usuariosCombo()
    {
        return $this->modelo->getUsuariosParaCombo();
    }

    public function localidadesMap()
    {
        return $this->modelo->getLocalidadesMap();
    }

    public function traerPuesto()
    {
        return $this->modelo->traerPuesto();
    }

    public function buscarDT()
    {
        header('Content-Type: application/json; charset=utf-8');

        // Parámetros DataTables
        $draw   = isset($_POST['draw'])   ? (int)$_POST['draw'] : 1;
        $start  = isset($_POST['start'])  ? max(0, (int)$_POST['start']) : 0;
        $length = isset($_POST['length']) ? min(200, max(1, (int)$_POST['length'])) : 10;

        // Búsqueda global
        $q = '';
        if (!empty($_POST['search']['value'])) {
            $q = trim((string)$_POST['search']['value']);
        }

        // Orden solicitado por DataTables
        $orderColKey = 'apellido';
        $orderDir    = 'asc';
        if (!empty($_POST['order'][0]['column']) && isset($_POST['columns'])) {
            $idx = (int)$_POST['order'][0]['column'];
            $orderColKey = $_POST['columns'][$idx]['data'] ?? 'apellido';
            $dir = strtolower($_POST['order'][0]['dir'] ?? 'asc');
            $orderDir = ($dir === 'desc') ? 'desc' : 'asc';
        }

        // Filtros del formulario del buscador
        $fEstado = $_POST['f_estado'] ?? null;                  // 'Activo' | 'Inactivo' | ''
        $fPuesto = isset($_POST['f_puesto']) && $_POST['f_puesto'] !== '' ? (int)$_POST['f_puesto'] : null; // id
        $fDesde  = $_POST['f_desde'] ?? null;                   // YYYY-MM-DD o ''
        $fHasta  = $_POST['f_hasta'] ?? null;                   // YYYY-MM-DD o ''

        // Normalizar strings vacíos a null
        foreach (['fEstado', 'fDesde', 'fHasta'] as $k) {
            if (isset($$k) && $$k === '') $$k = null;
        }

        try {
            // Tu constructor probablemente ya inicializa $this->modelo
            $res = $this->modelo->buscarEmpleadosDT(
                $q,
                $start,
                $length,
                $orderColKey,
                $orderDir,
                $fEstado,
                $fPuesto,
                $fDesde,
                $fHasta
            );

            echo json_encode([
                'draw'            => $draw,
                'recordsTotal'    => $res['total'],
                'recordsFiltered' => $res['filtrado'],
                'data'            => $res['rows']
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'draw'            => $draw,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => 'Error en búsqueda: ' . $e->getMessage()
            ]);
        }
    }
}
