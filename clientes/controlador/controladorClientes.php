<?php
require_once("../modelo/modeloClientes.php");

header('Content-Type: application/json; charset=utf-8');

function json_out($arr)
{
    echo json_encode($arr);
    exit;
}

$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

$mdl = new modeloClientes();

try {

    if ($accion === 'listar') {
        $rows = $mdl->listar();
        json_out(['ok' => true, 'data' => $rows]);
    }

    if ($accion === 'obtener') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) json_out(['ok' => false, 'msg' => 'ID inválido.']);

        $row = $mdl->obtenerPorId($id);
        if (!$row) json_out(['ok' => false, 'msg' => 'Cliente no encontrado.']);

        json_out(['ok' => true, 'data' => $row]);
    }

    if ($accion === 'actualizar') {
        $idCliente = (int)($_POST['idCliente'] ?? 0);
        $nombre    = trim($_POST['nombre'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $telefono  = trim($_POST['telefono'] ?? '');
        $calle     = trim($_POST['calle'] ?? '');
        $altura    = trim($_POST['altura'] ?? '');

        // Validaciones mínimas (alineadas con pedidos.js: los 5 campos obligatorios) :contentReference[oaicite:3]{index=3}
        if ($idCliente <= 0) json_out(['ok' => false, 'msg' => 'ID inválido.']);
        if ($nombre === '' || $email === '' || $telefono === '' || $calle === '' || $altura === '') {
            json_out(['ok' => false, 'msg' => 'Debe completar nombre, email, teléfono, calle y altura.']);
        }
        if (!ctype_digit((string)$altura) || (int)$altura <= 0) {
            json_out(['ok' => false, 'msg' => 'La altura debe ser un número mayor a cero.']);
        }

        $provincia = (int)($_POST['provincia'] ?? 0);
        $localidad = (int)($_POST['localidad'] ?? 0);

        if ($provincia <= 0 || $localidad <= 0) {
            json_out(['ok' => false, 'msg' => 'Provincia y localidad son obligatorias.']);
        }

        $ok = $mdl->actualizar([
            'idCliente' => $idCliente,
            'nombre'    => $nombre,
            'email'     => $email,
            'telefono'  => $telefono,
            'calle'     => $calle,
            'altura'    => $altura,
            'provincia' => $provincia,
            'localidad' => $localidad
        ]);


        json_out(['ok' => $ok, 'msg' => $ok ? 'Cliente actualizado correctamente.' : 'No se pudo actualizar el cliente.']);
    }

    if ($accion === 'eliminar') {
        $id = (int)($_POST['idCliente'] ?? 0);
        if ($id <= 0) json_out(['ok' => false, 'msg' => 'ID inválido.']);

        // Baja lógica
        $ok = $mdl->bajaLogica($id, 'Eliminado');
        json_out(['ok' => $ok, 'msg' => $ok ? 'Cliente eliminado (baja lógica) correctamente.' : 'No se pudo eliminar el cliente.']);
    }

    if ($accion === 'reactivar') {
        $id = (int)($_POST['idCliente'] ?? 0);
        if ($id <= 0) json_out(['ok' => false, 'msg' => 'ID inválido.']);

        $ok = $mdl->bajaLogica($id, 'Activo');
        json_out([
            'ok' => $ok,
            'msg' => $ok ? 'Cliente reactivado correctamente.' : 'No se pudo reactivar el cliente.'
        ]);
    }

    if ($accion === 'listarProvincias') {
        $rows = $mdl->listarProvincias();
        json_out(['ok' => true, 'data' => $rows]);
    }

    if ($accion === 'listarLocalidades') {
        $idProv = isset($_GET['id_provincia']) ? (int)$_GET['id_provincia'] : 0;
        if ($idProv <= 0) json_out(['ok' => false, 'msg' => 'Provincia inválida.']);

        $rows = $mdl->listarLocalidadesPorProvincia($idProv);
        json_out(['ok' => true, 'data' => $rows]);
    }



    json_out(['ok' => false, 'msg' => 'Acción no válida.']);
} catch (Exception $e) {
    json_out(['ok' => false, 'msg' => 'Error inesperado.', 'error' => $e->getMessage()]);
}
