<?php
// reparto/modelo/modeloVehiculo.php

class ModeloVehiculo
{
    private $pdo;

    public function __construct()
    {
        // Ruta relativa desde /reparto/modelo/ hacia /config/bd.php
        require_once("../../config/bd.php");
        $con = new bd();
        $this->pdo = $con->conexion();
    }

    /**
     * Listar vehículos (todos o sólo activos)
     */
    public function listarVehiculos($soloActivos = false)
    {
        $sql = "SELECT v.*, 
                       e.nombre, 
                       e.apellido
                FROM vehiculo v
                INNER JOIN empleados e ON v.idChofer = e.id_empleado
                WHERE v.eliminado = 0";

        if ($soloActivos) {
            $sql .= " AND v.estado = 'Activo'";
        }

        $sql .= " ORDER BY v.patente";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener un vehículo por ID
     */
    public function obtenerVehiculoPorId($idVehiculo)
    {
        $sql = "SELECT v.*, 
                       e.nombre, 
                       e.apellido
                FROM vehiculo v
                INNER JOIN empleados e ON v.idChofer = e.id_empleado
                WHERE v.idVehiculo = :id AND v.eliminado = 0";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $idVehiculo, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crear vehículo
     */
    public function crearVehiculo($data)
    {
        $sql = "INSERT INTO vehiculo
                    (patente, descripcion, marca, modelo, anio, capacidadKg, idChofer, estado)
                VALUES
                    (:patente, :descripcion, :marca, :modelo, :anio, :capacidadKg, :idChofer, :estado)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':patente', $data['patente']);
        $stmt->bindValue(':descripcion', $data['descripcion']);
        $stmt->bindValue(':marca', $data['marca']);
        $stmt->bindValue(':modelo', $data['modelo']);
        $stmt->bindValue(':anio', !empty($data['anio']) ? $data['anio'] : null, PDO::PARAM_INT);
        $stmt->bindValue(':capacidadKg', !empty($data['capacidadKg']) ? $data['capacidadKg'] : null);
        $stmt->bindValue(':idChofer', $data['idChofer'], PDO::PARAM_INT);
        $stmt->bindValue(':estado', $data['estado']);

        return $stmt->execute();
    }

    /**
     * Actualizar vehículo
     */
    public function actualizarVehiculo($idVehiculo, $data)
    {
        $sql = "UPDATE vehiculo
                SET patente = :patente,
                    descripcion = :descripcion,
                    marca = :marca,
                    modelo = :modelo,
                    anio = :anio,
                    capacidadKg = :capacidadKg,
                    idChofer = :idChofer,
                    estado = :estado
                WHERE idVehiculo = :id AND eliminado = 0";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':patente', $data['patente']);
        $stmt->bindValue(':descripcion', $data['descripcion']);
        $stmt->bindValue(':marca', $data['marca']);
        $stmt->bindValue(':modelo', $data['modelo']);
        $stmt->bindValue(':anio', !empty($data['anio']) ? $data['anio'] : null, PDO::PARAM_INT);
        $stmt->bindValue(':capacidadKg', !empty($data['capacidadKg']) ? $data['capacidadKg'] : null);
        $stmt->bindValue(':idChofer', $data['idChofer'], PDO::PARAM_INT);
        $stmt->bindValue(':estado', $data['estado']);
        $stmt->bindValue(':id', $idVehiculo, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Baja lógica del vehículo
     */
    public function desactivarVehiculo($idVehiculo)
    {
        $sql = "UPDATE vehiculo
                SET estado = 'Inactivo',
                    eliminado = 1
                WHERE idVehiculo = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $idVehiculo, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Listar choferes (operarios activos)
     */
    public function listarChoferes()
    {
        $sql = "SELECT e.id_empleado, e.nombre, e.apellido
                FROM empleados e
                WHERE e.estado = 'Activo'
                  AND e.eliminado = 0
                  AND e.id_puesto = 5  -- Operario
                ORDER BY e.apellido, e.nombre";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
