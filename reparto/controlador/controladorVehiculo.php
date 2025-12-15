<?php
// reparto/controlador/controladorVehiculo.php

require_once(__DIR__ . "/../modelo/modeloVehiculo.php");

class ControladorVehiculo
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new ModeloVehiculo();
    }

    public function listarVehiculos($soloActivos = false)
    {
        return $this->modelo->listarVehiculos($soloActivos);
    }

    public function listarChoferes()
    {
        return $this->modelo->listarChoferes();
    }

    public function obtenerVehiculoPorId($idVehiculo)
    {
        return $this->modelo->obtenerVehiculoPorId($idVehiculo);
    }

    public function crearVehiculo($data)
    {
        return $this->modelo->crearVehiculo($data);
    }

    public function actualizarVehiculo($idVehiculo, $data)
    {
        return $this->modelo->actualizarVehiculo($idVehiculo, $data);
    }

    public function desactivarVehiculo($idVehiculo)
    {
        return $this->modelo->desactivarVehiculo($idVehiculo);
    }
}
