<?php
session_start();
require_once("../controlador/controladorProductos.php");

$ctrl = new controladorProducto();
$ctrl->reporte();
