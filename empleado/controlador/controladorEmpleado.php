<?php
require_once("../modelo/modeloEmpleado.php");

class ControladorEmpleado
{
    private $modelo;

    public function __construct(?ModeloEmpleado $modelo = null)
    {
        $this->modelo = $modelo ?: new ModeloEmpleado();
    }

    /**
     * Devuelve el "perfil" del empleado vinculado al usuario logueado + un resumen
     * de licencias (placeholder hasta crear la transacción).
     */
    public function perfilEmpleado(?int $usuarioId): array
    {
        if (!$usuarioId) {
            return $this->fallbackNoAsociado();
        }

        $raw = $this->modelo->usuarioEmpleadoPorUsuarioId((int)$usuarioId);
        if (!$raw || empty($raw['emp_id'])) {
            return $this->fallbackNoAsociado();
        }

        // Antigüedad (años completos) en base a fecha_ingreso
        $antiguedad = $this->antiguedadEnAnios($raw['emp_fecha_ingreso'] ?? null);
        $diasCorrespondientes = $this->diasVacacionesPorAntiguedad($antiguedad);

        // Como aún no existe la tabla de licencias, dejamos 0 de base.
        // En el siguiente paso reemplazaremos por consultas reales.
        $diasTomadosAnio     = 0;
        $diasPendientesAnio  = 0;
        $diasRestantes       = max($diasCorrespondientes - $diasTomadosAnio - $diasPendientesAnio, 0);

        return [
            'asociado'         => true,

            // Usuario
            'user_id'          => (int)$raw['user_id'],
            'user_login'       => (string)$raw['user_usuario'],
            'user_nomyapellido' => (string)$raw['user_nomyapellido'],
            'user_estado'      => (string)$raw['user_estado'],
            'user_rol'         => $raw['user_rol'],

            // Empleado
            'emp_id'           => (int)$raw['emp_id'],
            'emp_nombre'       => (string)$raw['emp_nombre'],
            'emp_apellido'     => (string)$raw['emp_apellido'],
            'emp_sexo'         => (string)($raw['emp_sexo'] ?? ''),
            'emp_fecha_nac'    => (string)($raw['emp_fecha_nac'] ?? ''),
            'emp_dni'          => (string)$raw['emp_dni'],
            'emp_cuil'         => (string)($raw['emp_cuil'] ?? ''),
            'emp_email'        => (string)($raw['emp_email'] ?? ''),
            'emp_telefono'     => (string)($raw['emp_telefono'] ?? ''),
            'emp_direccion'    => (string)($raw['emp_direccion'] ?? ''),
            'emp_provincia'    => (string)($raw['emp_provincia'] ?? ''),
            'emp_localidad'    => (string)($raw['emp_localidad'] ?? ''),
            'emp_fecha_ingreso' => (string)$raw['emp_fecha_ingreso'],
            'emp_puesto'       => (string)($raw['emp_puesto'] ?? ''),
            'emp_estado'       => (string)$raw['emp_estado'],

            // Resumen licencias (placeholder)
            'resumen'          => [
                'anio'                => (int)date('Y'),
                'antiguedad_anios'    => $antiguedad,
                'dias_correspondientes' => $diasCorrespondientes,
                'dias_tomados'        => $diasTomadosAnio,
                'dias_pendientes'     => $diasPendientesAnio,
                'dias_restantes'      => $diasRestantes
            ],
        ];
    }

    public function perfilEmpleadoPorLogin(?string $userLogin): array
    {
        if (!$userLogin) {
            return $this->fallbackNoAsociado();
        }

        $raw = $this->modelo->obtenerUsuarioEmpleadoPorLogin($userLogin);
        if (!$raw || empty($raw['emp_id'])) {
            return $this->fallbackNoAsociado();
        }

        $ant = $this->antiguedadEnAnios($raw['emp_fecha_ingreso'] ?? null);
        $corr = $this->diasVacacionesPorAntiguedad($ant);
        $tom  = 0;
        $pend = 0;
        $rest = max($corr - $tom - $pend, 0);

        return [
            'asociado'          => true,

            // Usuario (mismos nombres que en perfilEmpleado)
            'user_id'           => (int)$raw['user_id'],
            'user_login'        => (string)$raw['user_usuario'],
            'user_nomyapellido' => (string)$raw['user_nomyapellido'],
            'user_estado'       => (string)$raw['user_estado'],
            'user_rol'          => $raw['user_rol'],

            // Empleado (prefijo emp_)
            'emp_id'            => (int)$raw['emp_id'],
            'emp_nombre'        => (string)$raw['emp_nombre'],
            'emp_apellido'      => (string)$raw['emp_apellido'],
            'emp_sexo'          => (string)($raw['emp_sexo'] ?? ''),
            'emp_fecha_nac'     => (string)($raw['emp_fecha_nac'] ?? ''),
            'emp_dni'           => (string)$raw['emp_dni'],
            'emp_cuil'          => (string)($raw['emp_cuil'] ?? ''),
            'emp_email'         => (string)($raw['emp_email'] ?? ''),
            'emp_telefono'      => (string)($raw['emp_telefono'] ?? ''),
            'emp_direccion'     => (string)($raw['emp_direccion'] ?? ''),
            'emp_provincia'     => (string)($raw['emp_provincia'] ?? ''),
            'emp_localidad'     => (string)($raw['emp_localidad'] ?? ''),
            'emp_fecha_ingreso' => (string)$raw['emp_fecha_ingreso'],
            'emp_puesto'        => (string)($raw['emp_puesto'] ?? ''),
            'emp_estado'        => (string)$raw['emp_estado'],

            // Resumen licencias (placeholder)
            'resumen'           => [
                'anio'                  => (int)date('Y'),
                'antiguedad_anios'      => $ant,
                'dias_correspondientes' => $corr,
                'dias_tomados'          => $tom,
                'dias_pendientes'       => $pend,
                'dias_restantes'        => $rest
            ],
        ];
    }


    private function antiguedadEnAnios(?string $fechaIngreso): int
    {
        if (!$fechaIngreso) return 0;
        try {
            $f = new DateTime($fechaIngreso);
            $hoy = new DateTime();
            $diff = $hoy->diff($f);
            return max(0, (int)$diff->y);
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * Escala estándar (ajustable) para Argentina:
     * 0-4 años: 14 días
     * 5-9 años: 21 días
     * 10-19 años: 28 días
     * 20+ años: 35 días
     */
    private function diasVacacionesPorAntiguedad(int $anios): int
    {
        if ($anios >= 20) return 35;
        if ($anios >= 10) return 28;
        if ($anios >= 5)  return 21;
        return 14;
    }

    private function fallbackNoAsociado(): array
    {
        return [
            'asociado'   => false,
            'user_login' => $_SESSION['user'] ?? '',
            'nombre'     => $_SESSION['nomyapellido'] ?? 'Empleado',
            'puesto'     => $_SESSION['rol'] ?? '',
            'estado'     => '',
        ];
    }
}
