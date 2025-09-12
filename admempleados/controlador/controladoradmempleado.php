<?php
require_once(__DIR__."/../modelo/modeloadmempleado.php");

class ControladorAdmEmpleado {
    private $modelo;

    public function __construct(){
        $this->modelo = new ModeloAdmEmpleado();
    }

    public function listar(){ return $this->modelo->listarTodos(); }
    public function obtener($id){ return $this->modelo->obtener($id); }

    public function guardar($p){
        return $this->modelo->insertar(
            $p['nombre'],$p['apellido'],$p['dni'],
            $p['email'] ?? null,$p['telefono'] ?? null,$p['direccion'] ?? null,
            $p['provincia'],$p['localidad'],
            $p['fecha_ingreso'],$p['puesto'],$p['estado'],
            $p['usuario_id'] ?? null
        );
    }

    public function actualizar($p){
        return $this->modelo->update(
            $p['id_empleado'],$p['nombre'],$p['apellido'],$p['dni'],
            $p['email'] ?? null,$p['telefono'] ?? null,$p['direccion'] ?? null,
            $p['provincia'],$p['localidad'],
            $p['fecha_ingreso'],$p['puesto'],$p['estado'],
            $p['usuario_id'] ?? null
        );
    }
    

    public function borrar($id){ return $this->modelo->eliminar($id); }
}
