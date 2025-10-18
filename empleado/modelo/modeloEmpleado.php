<?php
class ModeloEmpleado
{
    private $PDO;

    public function __construct()
    {
        // Mismo patrón que el resto de módulos (ajustá la ruta si fuese necesario)
        require_once("../../config/bd.php");
        $con = new bd();
        $this->PDO = $con->conexion();
    }

    /**
     * Trae datos del usuario y del empleado vinculado (si existe) por ID de usuario (usuarios.id).
     * Relación: empleados.usuario_id -> usuarios.id
     */
    public function usuarioEmpleadoPorUsuarioId(int $idUsuario): array
    {
        $sql = "
            SELECT
                u.id                    AS user_id,
                u.usuario               AS user_usuario,
                u.nomyapellido          AS user_nomyapellido,
                u.estado                AS user_estado,
                u.rol                   AS user_rol,

                e.id_empleado           AS emp_id,
                e.nombre                AS emp_nombre,
                e.apellido              AS emp_apellido,
                e.sexo                  AS emp_sexo,
                e.id_estado_civil       AS emp_estado_civil,
                e.fecha_nac             AS emp_fecha_nac,
                e.dni                   AS emp_dni,
                e.cuil                  AS emp_cuil,
                e.email                 AS emp_email,
                e.telefono              AS emp_telefono,
                e.direccion             AS emp_direccion,
                e.provincia             AS emp_provincia_id,
                e.localidad             AS emp_localidad_id,
                e.fecha_ingreso         AS emp_fecha_ingreso,
                e.id_puesto             AS emp_id_puesto,
                e.estado                AS emp_estado,
                e.eliminado             AS emp_eliminado,
                e.fecha_baja            AS emp_fecha_baja,

                p.descrPuesto           AS emp_puesto,
                prov.provincia          AS emp_provincia,
                loc.localidad           AS emp_localidad
            FROM usuarios u
            LEFT JOIN empleados e       ON e.usuario_id = u.id
            LEFT JOIN puesto p          ON p.idPuesto = e.id_puesto
            LEFT JOIN provincias prov   ON prov.id_provincia = e.provincia
            LEFT JOIN localidades loc   ON loc.id_localidad = e.localidad
            WHERE u.id = :uid
            LIMIT 1;
        ";

        $st = $this->PDO->prepare($sql);
        $st->execute([':uid' => $idUsuario]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }

    public function obtenerUsuarioEmpleadoPorLogin(string $userLogin): array
    {
        $sql = "
        SELECT
            u.id                   AS user_id,
            u.usuario              AS user_usuario,
            u.nomyapellido         AS user_nomyapellido,
            u.estado               AS user_estado,
            u.rol                  AS user_rol,

            e.id_empleado          AS emp_id,
            e.nombre               AS emp_nombre,
            e.apellido             AS emp_apellido,
            e.sexo                 AS emp_sexo,
            e.fecha_nac            AS emp_fecha_nac,
            e.dni                  AS emp_dni,
            e.cuil                 AS emp_cuil,
            e.email                AS emp_email,
            e.telefono             AS emp_telefono,
            e.direccion            AS emp_direccion,
            e.provincia            AS emp_provincia_id,
            e.localidad            AS emp_localidad_id,
            e.fecha_ingreso        AS emp_fecha_ingreso,
            e.id_puesto            AS emp_id_puesto,
            e.estado               AS emp_estado,
            e.fecha_baja           AS emp_fecha_baja,

            p.descrPuesto          AS emp_puesto,
            prov.provincia         AS emp_provincia,
            loc.localidad          AS emp_localidad
        FROM usuarios u
        LEFT JOIN empleados e     ON e.usuario_id = u.id
        LEFT JOIN puesto p        ON p.idPuesto = e.id_puesto
        LEFT JOIN provincias prov ON prov.id_provincia = e.provincia
        LEFT JOIN localidades loc ON loc.id_localidad = e.localidad
        WHERE u.usuario = :ulogin
        LIMIT 1;
    ";

        $st = $this->PDO->prepare($sql);
        $st->execute([':ulogin' => $userLogin]);
        return $st->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}
