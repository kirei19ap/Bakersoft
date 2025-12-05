<?php
session_start();
require_once '../controlador/ControladorProductos.php';

$ctrl = new ControladorProducto();
$ctrl->crear(); // procesa POST y redirige a /productos/index.php con flash
