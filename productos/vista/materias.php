<?php
session_start();
require_once __DIR__ . '/../controlador/controladorProductos.php';

$ctrl = new controladorProducto();
$ctrl->materias(); // imprime JSON
